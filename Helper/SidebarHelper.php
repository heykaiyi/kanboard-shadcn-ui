<?php

namespace Kanboard\Plugin\Shadcn\Helper;

use Kanboard\Core\Base;

/**
 * Data for the shadcn sidebar.
 *
 * Kanboard renders the template:layout:top hook without any parameters, so
 * unlike a normal view the sidebar template is never handed the current
 * project. Everything it needs is resolved here instead, from the request
 * and the router, which is also where the "is this nav item active?" test
 * belongs.
 */
class SidebarHelper extends Base
{
    /**
     * Number of projects listed before the "More" link takes over.
     */
    const PROJECT_LIMIT = 6;

    private $project;

    /**
     * The sidebar is navigation for a signed-in user. Anonymous requests —
     * the login screen and public boards — get nothing.
     */
    public function isVisible()
    {
        return $this->userSession->isLogged();
    }

    /**
     * The project the current page belongs to, or an empty array.
     *
     * Most project-scoped routes carry project_id. Task routes do not always,
     * so a task_id is resolved back to its project.
     */
    public function getProject()
    {
        if ($this->project !== null) {
            return $this->project;
        }

        $this->project = array();
        $projectId = $this->request->getIntegerParam('project_id');

        if ($projectId === 0) {
            $taskId = $this->request->getIntegerParam('task_id');

            if ($taskId > 0) {
                $projectId = $this->taskFinderModel->getProjectId($taskId);
            }
        }

        /* Only projects this user may actually open.
         *
         * template:layout:top is rendered on the access-denied page too, so
         * without this the sidebar's brand and the breadcrumb happily named
         * a project — and, via task_id, the title of a single task — that
         * Kanboard had just answered 403 for. isUserAllowed() returns true
         * for administrators and for members, which is the same test the
         * controllers apply. */
        if ($projectId > 0 && $this->projectPermissionModel->isUserAllowed($projectId, $this->userSession->getId())) {
            $project = $this->projectModel->getById($projectId);

            if (! empty($project)) {
                $this->project = $project;
            }
        }

        return $this->project;
    }

    /**
     * The active search filter, so switching view keeps the current filter.
     * Read here because a template resolves $this->request through the helper
     * registry, where there is no such helper.
     */
    public function getSearchQuery()
    {
        return $this->request->getStringParam('search');
    }

    /**
     * Projects the current user can open, as id => name.
     */
    public function getProjects()
    {
        return $this->projectUserRoleModel->getActiveProjectsByUser($this->userSession->getId());
    }


    /**
     * Same test as AppHelper::checkMenuSelection(), returning a boolean so the
     * template can compose a class list rather than an attribute string.
     */
    public function isActive($controller, $action = '', $plugin = '')
    {
        if (strtolower($this->router->getController()) !== strtolower($controller)) {
            return false;
        }

        if ($action !== '' && strtolower($this->router->getAction()) !== strtolower($action)) {
            return false;
        }

        if ($plugin !== '' && strtolower($this->router->getPlugin()) !== strtolower($plugin)) {
            return false;
        }

        return true;
    }

    /**
     * Whether a sibling plugin is installed, so its view is only offered when
     * it can actually be reached. Checked by class rather than through the
     * plugin loader so this works no matter the load order.
     */
    /**
     * Where the documentation link should point.
     *
     * Kanboard's own DocumentationController serves the bundled help pages
     * about Kanboard itself — useful, but not somewhere to write anything.
     * With the Wiki plugin installed there is a real place for project
     * documentation, so the link goes there instead.
     */
    /**
     * Kanboard's own documentation, which is a website rather than a page of
     * this instance — so it opens in a tab of its own and is never the
     * "current" item in the sidebar. The copy bundled with the application is
     * a snapshot of the same site, and always older than it.
     */
    const DOCUMENTATION_URL = 'https://docs.kanboard.org/';

    public function getDocumentationUrl()
    {
        return self::DOCUMENTATION_URL;
    }

    public function isDocumentationActive()
    {
        return false;
    }

    public function hasPlugin($name)
    {
        return class_exists('\\Kanboard\\Plugin\\'.$name.'\\Plugin');
    }

    /**
     * Everything the command palette can reach, as a flat list.
     *
     * Built server-side because it is the only place that knows which
     * projects this user may open and which plugins are installed.
     */
    public function getCommandItems()
    {
        $userId = $this->userSession->getId();
        $items = array();

        foreach ($this->getDashboardItems() as $item) {
            $items[] = array(
                'group' => t('Navigation'),
                'label' => $item['label'],
                'url' => $this->helper->url->href($item['controller'], $item['action'], $item['params']),
                'icon' => $item['icon'],
            );
        }

        /* Switching project keeps the view you were reading. Kanboard's own
         * switcher always landed on the board, which meant leaving the Gantt
         * chart of one project put you on the board of the next. */
        list($viewController, $viewAction) = $this->getProjectViewRoute();
        $currentProject = $this->getProject();

        foreach ($this->getProjects() as $projectId => $projectName) {
            $items[] = array(
                'group' => t('Projects'),
                'label' => $projectName,
                'url' => $this->helper->url->href($viewController, $viewAction, array('project_id' => $projectId)),
                'icon' => 'folder',
                'current' => ! empty($currentProject) && (int) $currentProject['id'] === (int) $projectId,
            );
        }

        $settings = array(
            array('__doc__', '', t('Documentation'), 'life-ring'),
        );

        if ($this->userSession->isAdmin()) {
            $settings[] = array('ConfigController', 'index', t('Settings'), 'cog');
            $settings[] = array('UserListController', 'show', t('Users management'), 'user');
            $settings[] = array('PluginController', 'show', t('Plugins'), 'cubes');
        }

        foreach ($settings as $entry) {
            $items[] = array(
                'group' => t('Settings'),
                'label' => $entry[2],
                'url' => $entry[0] === '__doc__'
                    ? $this->getDocumentationUrl()
                    : $this->helper->url->href($entry[0], $entry[1], array()),
                'icon' => $entry[3],
                // The documentation is a website, so it opens beside the
                // instance rather than over it.
                'external' => $entry[0] === '__doc__',
            );
        }

        /* Two groups can legitimately reach the same screen; the palette
         * should still offer it once. */
        $seen = array();
        $unique = array();

        foreach ($items as $item) {
            if (! isset($seen[$item['url']])) {
                $seen[$item['url']] = true;
                $unique[] = $item;
            }
        }

        return $unique;
    }

    /**
     * The four numbers on the dashboard's welcome row.
     *
     * All of them are real: Kanboard already scopes tasks to a user, so
     * nothing here is invented for the sake of filling a tile.
     */
    public function getDashboardStats()
    {
        $userId = $this->userSession->getId();
        $today = strtotime('today');
        $week = $today + (7 * 86400);

        $projects = count($this->projectUserRoleModel->getActiveProjectsByUser($userId));
        $open = $this->taskFinderModel->getUserQuery($userId)->count();
        $overdue = count($this->taskFinderModel->getOverdueTasksByUser($userId));
        $soon = $this->taskFinderModel->getUserQuery($userId)
            ->gte(\Kanboard\Model\TaskModel::TABLE.'.date_due', $today)
            ->lte(\Kanboard\Model\TaskModel::TABLE.'.date_due', $week)
            ->count();

        return array(
            array('label' => t('My projects'), 'value' => $projects, 'icon' => 'folder', 'tone' => 'neutral',
                  'url' => $this->helper->url->href('DashboardController', 'projects', array('user_id' => $userId))),
            array('label' => t('Open tasks'), 'value' => $open, 'icon' => 'list', 'tone' => 'primary',
                  'url' => $this->helper->url->href('DashboardController', 'tasks', array('user_id' => $userId))),
            array('label' => t('Due this week'), 'value' => $soon, 'icon' => 'calendar', 'tone' => 'success',
                  'url' => $this->helper->url->href('DashboardController', 'tasks', array('user_id' => $userId))),
            array('label' => t('Overdue'), 'value' => $overdue, 'icon' => 'calendar-times-o', 'tone' => 'warning',
                  'url' => $this->helper->url->href('DashboardController', 'tasks', array('user_id' => $userId))),
        );
    }

    /**
     * The name to greet with.
     */
    public function getGreetingName()
    {
        return $this->helper->user->getFullname();
    }

    /**
     * The dashboard's own sections, for when no project is in context.
     *
     * Kanboard puts these in a per-page .sidebar; with a global sidebar they
     * belong in it, otherwise the rail shows a single item and the page grows
     * a second nav of its own.
     */
    public function isDashboardRoute()
    {
        return $this->isActive('DashboardController');
    }

    public function getDashboardItems()
    {
        $userId = $this->userSession->getId();

        $items = array(
            array('icon' => 'eye', 'label' => t('Overview'),
                  'controller' => 'DashboardController', 'action' => 'show',
                  'params' => array('user_id' => $userId),
                  'active' => $this->isActive('DashboardController', 'show')),
            array('icon' => 'folder', 'label' => t('My projects'),
                  'controller' => 'DashboardController', 'action' => 'projects',
                  'params' => array('user_id' => $userId),
                  'active' => $this->isActive('DashboardController', 'projects')),
            array('icon' => 'list', 'label' => t('My tasks'),
                  'controller' => 'DashboardController', 'action' => 'tasks',
                  'params' => array('user_id' => $userId),
                  'active' => $this->isActive('DashboardController', 'tasks')),
            array('icon' => 'check-square-o', 'label' => t('My subtasks'),
                  'controller' => 'DashboardController', 'action' => 'subtasks',
                  'params' => array('user_id' => $userId),
                  'active' => $this->isActive('DashboardController', 'subtasks')),
            array('icon' => 'cubes', 'label' => t('Projects management'),
                  'controller' => 'ProjectListController', 'action' => 'show',
                  'params' => array(),
                  'active' => $this->isActive('ProjectListController')),
            /* The activity stream is something you glance at, not a place you
             * navigate to — it opens over the page. Kanboard's own modal
             * plumbing does the work; the theme turns any dialog into a
             * bottom drawer below 768px. */
        );

        return $items;
    }

    /**
     * What the rail shows on a page that already has a nav of its own —
     * settings, a profile, the plugin directory. Mirroring that page's menu
     * would only say the same thing twice, so this is just the way home.
     */
    public function getMinimalItems()
    {
        $userId = $this->userSession->getId();

        return array(
            array('icon' => 'dashboard', 'label' => t('Dashboard'),
                  'controller' => 'DashboardController', 'action' => 'show',
                  'params' => array('user_id' => $userId),
                  'active' => false),
        );
    }

    /**
     * Whether the signed-in user is an administrator, for the mark next to
     * their name.
     */
    public function isAdmin()
    {
        return $this->userSession->isAdmin();
    }

    /**
     * The signed-in user's avatar at a size worth showing.
     *
     * The header's user menu asks for 20px, which is what the sidebar footer
     * would otherwise scale up to 32 and blur.
     */
    public function renderUserAvatar($size = 48)
    {
        $user = $this->userModel->getById($this->userSession->getId());

        if (empty($user)) {
            return '';
        }

        return $this->helper->avatar->render(
            $user['id'],
            $user['username'],
            isset($user['name']) ? $user['name'] : '',
            isset($user['email']) ? $user['email'] : '',
            isset($user['avatar_path']) ? $user['avatar_path'] : '',
            'avatar-inline',
            $size
        );
    }

    /**
     * The trail for the top bar.
     *
     * Kanboard puts a single pre-joined string in <h1> ("Project > Swimlane >
     * Column"), which no stylesheet can split into crumbs. This rebuilds it
     * from the route instead, as a list of ['label' => , 'url' => ] where a
     * null url marks the current page.
     */
    public function getBreadcrumb()
    {
        $project = $this->getProject();

        /* Every trail is rooted at the dashboard, drawn as a house. A
         * breadcrumb whose first item is already the page you are on is not
         * a trail, and on a phone the icon costs almost no width. */
        $crumbs = array(array(
            'label' => t('Home'),
            'url'   => $this->helper->url->href('DashboardController', 'show'),
            'home'  => true,
        ));

        if (empty($project)) {
            /* A lone page title says nothing that the <h1> does not already
             * say, so the section it belongs to goes in front of it. */
            $group = $this->getSectionGroup();
            $section = $this->getSectionLabel();

            if (! empty($group) && $group['label'] !== $section) {
                $crumbs[] = $group;
            }

            if ($section !== '') {
                $crumbs[] = array('label' => $section, 'url' => null);
            }

            return $crumbs;
        }

        $crumbs[] = array(
            'label' => t('Projects'),
            'url' => $this->helper->url->href('ProjectListController', 'show'),
        );

        $crumbs[] = array(
            'label' => $project['name'],
            'url' => $this->helper->url->href('BoardViewController', 'show', array('project_id' => $project['id'])),
        );

        $taskId = $this->request->getIntegerParam('task_id');

        if ($taskId > 0) {
            $task = $this->taskFinderModel->getById($taskId);

            if (! empty($task)) {
                $crumbs[] = array('label' => '#'.$task['id'].' '.$task['title'], 'url' => null);

                return $crumbs;
            }
        }

        $view = $this->getSectionLabel();

        if ($view !== '') {
            $crumbs[] = array('label' => $view, 'url' => null);
        }

        return $crumbs;
    }

    /**
     * A label for wherever we currently are, keyed off the route so it stays
     * in step with the sidebar's own active state.
     */
    /**
     * The section an inner page belongs to, so the trail carries a type and
     * not just a title. Empty when the page is a section in its own right.
     */
    private function getSectionGroup()
    {
        $settings = array(
            'userlistcontroller', 'grouplistcontroller', 'plugincontroller',
            'linkcontroller', 'currencycontroller', 'tagcontroller',
            'actioncontroller',
        );

        $controller = strtolower($this->router->getController());

        if (in_array($controller, $settings, true)) {
            return array(
                'label' => t('Settings'),
                'url'   => $this->helper->url->href('ConfigController', 'index'),
            );
        }

        if (in_array($controller, array('userviewcontroller', 'usercredentialcontroller', 'twofactorcontroller'), true)) {
            return array(
                'label' => t('Users'),
                'url'   => $this->helper->url->href('UserListController', 'show'),
            );
        }

        return array();
    }

    /**
     * The project view the current page is, or the board when the page is
     * not a project view at all. Only the five the sidebar itself offers —
     * a project's settings screen is not a view to carry across.
     */
    private function getProjectViewRoute()
    {
        $views = array(
            'ProjectOverviewController' => 'show',
            'BoardViewController' => 'show',
            'TaskListController' => 'show',
            'CalendarController' => 'show',
            'TaskGanttController' => 'show',
        );

        $controller = $this->router->getController();

        foreach ($views as $name => $action) {
            if (strtolower($name) === strtolower($controller)) {
                return array($name, $action);
            }
        }

        return array('BoardViewController', 'show');
    }

    private function getSectionLabel()
    {
        $map = array(
            'DashboardController' => t('Dashboard'),
            'ProjectOverviewController' => t('Overview'),
            'BoardViewController' => t('Board'),
            'TaskListController' => t('List'),
            'CalendarController' => t('Calendar'),
            'TaskGanttController' => t('Gantt'),
            'AnalyticController' => t('Analytics'),
            'ProjectListController' => t('Projects'),
            'ConfigController' => t('Settings'),
            'UserListController' => t('Users'),
            'GroupListController' => t('Groups'),
            'PluginController' => t('Plugins'),
            'ActivityController' => t('Activity stream'),
            'SearchController' => t('Search'),
            'UserViewController' => t('Profile'),
            'UserCredentialController' => t('Edit user'),
            'TwoFactorController' => t('Two factor authentication'),
            'LinkController' => t('Link labels'),
            'CurrencyController' => t('Currency rates'),
            'TagController' => t('Tags'),
            'ActionController' => t('Automatic actions'),
            'WikiController' => t('Wiki'),
        );

        /* The dashboard's sections all live on one controller, so the action
         * is what tells them apart. */
        $actions = array(
            'projects' => t('My projects'),
            'tasks' => t('My tasks'),
            'subtasks' => t('My subtasks'),
            'activity' => t('My activity stream'),
        );

        $controller = $this->router->getController();
        $action = strtolower($this->router->getAction());

        if (strtolower($controller) === 'dashboardcontroller' && isset($actions[$action])) {
            return $actions[$action];
        }

        foreach ($map as $name => $label) {
            if (strtolower($name) === strtolower($controller)) {
                return $label;
            }
        }

        return '';
    }

    /**
     * The signed-in user's email, for the footer's NavUser line. UserHelper
     * exposes the name but not the address, so it is read from the record.
     */
    public function getUserEmail()
    {
        $user = $this->userModel->getById($this->userSession->getId());

        return empty($user['email']) ? '' : $user['email'];
    }

    /**
     * The instance's own name. Kanboard has no configurable application
     * title, so the plugin carries one — set under Settings → Appearance,
     * and read from here by every mark, the login screen and the outgoing
     * mail, so there is still exactly one place it comes from.
     *
     * A name, so it is not sent through t().
     */
    public function getBrandTitle()
    {
        return $this->helper->shadcnBrand->getTitle();
    }

    /**
     * The name with room to spare — the phone's top bar cannot hold the
     * full one without cutting it mid-word. Derived rather than written
     * out a second time, so it follows getBrandTitle().
     */
    public function getBrandShortTitle()
    {
        $parts = preg_split('/\s+/u', trim($this->getBrandTitle()), 2);

        return $parts[0];
    }

    /**
     * The line under the name. Set under Settings → Appearance, and
     * translated only when it has been left at the default — and only
     * shown where there is room for it.
     */
    public function getBrandSubtitle()
    {
        return $this->helper->shadcnBrand->getSubtitle();
    }

    /**
     * Where the brand points. Signed in that is the dashboard; on the auth
     * screens there is nothing behind the login form, so it is the site
     * root.
     */
    public function getBrandUrl()
    {
        return $this->userSession->isLogged()
            ? $this->helper->url->href('DashboardController', 'show')
            : $this->helper->url->base();
    }
}

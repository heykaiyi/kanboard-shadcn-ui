<li <?= $this->app->checkMenuSelection('ConfigController', 'show', 'Shadcn') ?>>
    <?= $this->url->link(t('Appearance'), 'ConfigController', 'show', array('plugin' => 'Shadcn')) ?>
</li>

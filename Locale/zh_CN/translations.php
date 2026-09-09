<?php

/**
 * Simplified Chinese for the strings this plugin introduces.
 *
 * Mainland vocabulary throughout — 项目 rather than 專案, 保存 rather than
 * 儲存 — because that is what Kanboard's own zh_CN uses and the two have to
 * read as one interface. Everything not listed here comes from that file.
 */
return array(
    /* The sidebar, the top bar and the command palette. */
    'Navigation' => '导航',
    'Breadcrumb' => '面包屑导航',
    'Toggle navigation' => '展开或收起导航',
    'Home' => '首页',
    'All' => '全部',
    'More' => '更多',
    'New' => '新建',
    'Close' => '关闭',
    'Current' => '当前',
    'No results' => '没有结果',
    'Calendar' => '日历',
    'Gantt' => '甘特图',
    'Wiki' => '知识库',
    'Passkeys' => '通行密钥',

    /* The dashboard's welcome row. */
    'Welcome back, %s' => '欢迎回来，%s',
    'Your projects, what is open, and what is due next.' => '你的项目、进行中的任务，以及接下来要到期的事。',
    'Due this week' => '本周到期',
    'Overdue' => '已逾期',

    /* The auth screens. */
    'Login to your account' => '登录你的账号',
    'Enter your username below to sign in' => '请在下方输入你的用户名以登录',
    'Or continue with' => '或使用以下方式',
    'Terms of Service' => '服务条款',
    'Privacy Policy' => '隐私政策',
    'Show password' => '显示密码',
    'Hide password' => '隐藏密码',

    /* Settings → Appearance. */
    'Appearance' => '外观',
    'Application name' => '网站名称',
    'Tagline' => '副标题',
    'Shown beside the mark in the sidebar, on the login screen and in every outgoing email.' => '会出现在侧边栏的标志旁、登录页面，以及每一封发出的通知邮件。',
    'The small line under the name in the sidebar. Leave empty for the default.' => '侧边栏名称下方的小字。留空就用默认值。',

    'Colors' => '色彩',
    'Two hex values: the one the interface acts with, and the one it rests on. Each of them can answer twice — once for the light palette, once for the dark.' => '两组十六进制色值：界面用来动作的那一个，和它安放其上的那一个。两个都可以分别设置浅色与深色。',
    'Accent color' => '主题色',
    'Buttons, links, the active sidebar item, focus borders and the email header all follow it.' => '按钮、链接、侧边栏当前项、聚焦边框与邮件标头都会跟着变。',
    'Secondary color' => '次要色',
    'The plate under the quiet controls: the search box and the bell in the top bar, and the chips inside a multi-select.' => '安静控件下面的那块底：顶栏的搜索框与通知铃，以及多选框里的标签。',
    'Sidebar background' => '侧边栏底色',
    'The sidebar\'s own surface, behind the navigation.' => '侧边栏自己的底，衬在导航后面。',
    'Muted surface' => '柔和底色',
    'The quiet fill: list headers, neutral chips, and the cards a message or an activity entry sits on.' => '安静的填色：列表表头、中性标签，以及消息与动态记录所在的卡片。',
    'Hover surface' => '悬停底色',
    'Where the pointer is: menu items, rows, and outline buttons.' => '指针所在之处：菜单项、行，以及描边按钮。',
    'Border' => '边框色',
    'Every hairline in the interface, including the one a field draws around itself.' => '界面里的每一条细线，包括输入框给自己画的那一条。',
    'Leave a field empty for the theme default; leave a dark field empty and the light colour is used at night too.' => '留空就用主题默认色；深色留空的话，夜里就沿用浅色那一个。',
    'Text on top of a colour is chosen automatically, whichever of black or white is readable.' => '色块上的文字会自动在黑与白之间选一个看得清的。',
    'Which palette a person sees is their own Theme preference — this changes what each one looks like, not which one they get.' => '每个人看到哪一套，取决于他自己的「主题」设置；这里改的是两套各自长什么样，而不是谁看到哪一套。',
    'That is not a valid colour. Use a hex value such as #1145af.' => '这不是有效的色值，请填类似 #1145af 的十六进制值。',
    'Light' => '浅色',
    'Dark' => '深色',
    'Reset' => '重置',
    'A link' => '链接',

    'Logo' => '标志',
    'Favicon' => '网站图标',
    'Choose an image' => '选择图片',
    'No file selected' => '尚未选择文件',
    'Restore the default logo' => '恢复默认标志',
    'Restore the default favicon' => '恢复默认网站图标',
    'PNG, JPG, GIF, WebP or SVG, up to %d KB. A square image, since it is drawn in a square plate.' => 'PNG、JPG、GIF、WebP 或 SVG，上限 %d KB。请用正方形的图，它会被放进一个方形底板里。',
    'PNG, JPG, GIF, WebP or SVG, up to %d KB. Square, and legible at 16 pixels.' => 'PNG、JPG、GIF、WebP 或 SVG，上限 %d KB。请用正方形，并确认缩到 16 像素还看得清。',
    'An SVG is used everywhere except in email, which no client renders it in — the bundled PNG stands in there.' => 'SVG 在各处都能用，只有邮件例外——没有邮件客户端画得出来，那里会改用内置的 PNG。',
    'Leave this empty and the logo is used as the tab icon.' => '留空的话就直接拿标志当标签页图标。',
    'No .ico: a browser handed both an ICO and the SVG Kanboard declares takes the SVG, so an ICO here would never be the icon you see.' => '不支持 .ico：浏览器同时拿到 ICO 与 Kanboard 声明的 SVG 时会选 SVG，所以放 ICO 在这里永远不会是你看到的那个图标。',

    'Lockup' => '显示方式',
    'Mark only' => '只有标志',
    'Mark and name' => '标志加名称',
    'Mark, name and tagline' => '标志、名称加副标题',
    'Applies to the sidebar, the phone top bar and the login screen. Email always carries the name, whatever is chosen here.' => '会应用在侧边栏、手机版顶栏与登录页。邮件一律带名称，不受这里影响。',
    'Mark size' => '标志大小',
    'A percentage of the size the theme draws the mark at. The sidebar beside this screen follows the slider.' => '相对于主题原本尺寸的百分比。拖动时旁边侧边栏的标志会跟着变。',

    /* Upload failures, shown as flash messages. */
    'The image must be smaller than %d KB.' => '图片必须小于 %d KB。',
    'Allowed formats: %s.' => '可用的格式：%s。',
    'This file is not a valid image.' => '这个文件不是有效的图片。',
    'This SVG contains script and was refused.' => '这个 SVG 含有脚本，已拒绝上传。',
    'The file could not be uploaded.' => '文件上传失败。',
    'Unknown image.' => '未知的图片字段。',

    /* Outgoing mail. */
    'Open the task' => '打开任务',
    'Open the board' => '打开看板',
    'You are receiving this email because you are subscribed to notifications on %s.' => '你收到这封邮件，是因为你订阅了 %s 的通知。',
    'This message may contain confidential information. If it was not meant for you, please delete it and do not forward it.' => '这封邮件可能包含机密信息。如果不是寄给你的，请删除它，不要转发。',

    /* The plugin's own description, on Settings → Plugins. */
    'shadcn/ui design language and HugeIcons for the whole Kanboard interface' => '为整个 Kanboard 界面带来 shadcn/ui 的设计语言与 HugeIcons',
    'My project management tool' => '我的项目管理工具',

    /* Three strings Kanboard's own file for this language leaves out. */
    '%s\'s activity' => '%s 的动态',
    'Search task title, description, and comments by default' => '默认同时搜索任务标题、描述与评论',
    'You don\'t have the permission to move this task' => '你没有权限移动这个任务',
);

<?php

/**
 * Japanese for the strings this plugin introduces.
 *
 * Only those: everything else on screen comes from Kanboard's own ja_JP,
 * which is complete. Kanboard falls back to the English source for anything
 * missing, so a language without a file here is still consistent — it is
 * simply English in the places this plugin speaks.
 */
return array(
    /* The sidebar, the top bar and the command palette. */
    'Navigation' => 'ナビゲーション',
    'Breadcrumb' => 'パンくずリスト',
    'Toggle navigation' => 'ナビゲーションの開閉',
    'Home' => 'ホーム',
    'All' => 'すべて',
    'More' => 'もっと見る',
    'New' => '新規',
    'Close' => '閉じる',
    'Current' => '現在',
    'No results' => '該当なし',
    'Calendar' => 'カレンダー',
    'Gantt' => 'ガント',
    'Wiki' => 'Wiki',
    'Passkeys' => 'パスキー',

    /* The dashboard's welcome row. */
    'Welcome back, %s' => 'おかえりなさい、%s',
    'Your projects, what is open, and what is due next.' => 'あなたのプロジェクト、進行中のタスク、そして次に期限が来るもの。',
    'Due this week' => '今週締切',
    'Overdue' => '期限超過',

    /* The auth screens. */
    'Login to your account' => 'アカウントにログイン',
    'Enter your username below to sign in' => 'ユーザー名を入力してサインインしてください',
    'Or continue with' => 'または次の方法で',
    'Terms of Service' => '利用規約',
    'Privacy Policy' => 'プライバシーポリシー',
    'Show password' => 'パスワードを表示',
    'Hide password' => 'パスワードを非表示',

    /* Settings → Appearance. */
    'Appearance' => '外観',
    'Application name' => 'アプリケーション名',
    'Tagline' => 'タグライン',
    'Shown beside the mark in the sidebar, on the login screen and in every outgoing email.' => 'サイドバーのマークの横、ログイン画面、そして送信されるすべてのメールに表示されます。',
    'The small line under the name in the sidebar. Leave empty for the default.' => 'サイドバーの名前の下に出る小さな一行。空欄なら既定値を使います。',

    'Colors' => 'カラー',
    'Two hex values: the one the interface acts with, and the one it rests on. Each of them can answer twice — once for the light palette, once for the dark.' => '2 つの 16 進数：インターフェイスが動作に使う色と、その下地になる色。どちらもライトとダークで個別に指定できます。',
    'Accent color' => 'アクセントカラー',
    'Buttons, links, the active sidebar item, focus borders and the email header all follow it.' => 'ボタン、リンク、サイドバーの現在項目、フォーカスの枠線、メールのヘッダーがこれに従います。',
    'Secondary color' => 'セカンダリカラー',
    'The plate under the quiet controls: the search box and the bell in the top bar, and the chips inside a multi-select.' => '主役ではないコントロールの下地：トップバーの検索ボックスとベル、そして複数選択の中のチップ。',
    'Sidebar background' => 'サイドバーの背景',
    'The sidebar\'s own surface, behind the navigation.' => 'ナビゲーションの背後にある、サイドバー自身の面。',
    'Muted surface' => '控えめな面',
    'The quiet fill: list headers, neutral chips, and the cards a message or an activity entry sits on.' => '静かな塗り：リストのヘッダー、無彩色のチップ、メッセージやアクティビティが載るカード。',
    'Hover surface' => 'ホバーの面',
    'Where the pointer is: menu items, rows, and outline buttons.' => 'ポインタのある場所：メニュー項目、行、アウトラインボタン。',
    'Border' => '罫線',
    'Every hairline in the interface, including the one a field draws around itself.' => 'インターフェイスのすべての細線。フィールドが自分の周りに描くものも含みます。',
    'Leave a field empty for the theme default; leave a dark field empty and the light colour is used at night too.' => '空欄ならテーマの既定色。ダーク欄を空にすると、夜もライトの色が使われます。',
    'Text on top of a colour is chosen automatically, whichever of black or white is readable.' => '色の上に載る文字は、黒と白のうち読める方が自動的に選ばれます。',
    'Which palette a person sees is their own Theme preference — this changes what each one looks like, not which one they get.' => 'どちらのパレットを見るかは各自の「テーマ」設定です。ここで変わるのは、それぞれの見え方であって、誰がどちらを見るかではありません。',
    'That is not a valid colour. Use a hex value such as #1145af.' => '有効な色ではありません。#1145af のような 16 進数で指定してください。',
    'Light' => 'ライト',
    'Dark' => 'ダーク',
    'Reset' => 'リセット',
    'A link' => 'リンク',

    'Logo' => 'ロゴ',
    'Favicon' => 'ファビコン',
    'Choose an image' => '画像を選択',
    'No file selected' => 'ファイルが選択されていません',
    'Restore the default logo' => '既定のロゴに戻す',
    'Restore the default favicon' => '既定のファビコンに戻す',
    'PNG, JPG, GIF, WebP or SVG, up to %d KB. A square image, since it is drawn in a square plate.' => 'PNG、JPG、GIF、WebP、SVG、%d KB まで。正方形の台座に描かれるので、正方形の画像を使ってください。',
    'PNG, JPG, GIF, WebP or SVG, up to %d KB. Square, and legible at 16 pixels.' => 'PNG、JPG、GIF、WebP、SVG、%d KB まで。正方形で、16 ピクセルでも判別できるものを。',
    'An SVG is used everywhere except in email, which no client renders it in — the bundled PNG stands in there.' => 'SVG はメール以外のすべてで使われます。メールでは描画するクライアントがないため、同梱の PNG が代わりに出ます。',
    'Leave this empty and the logo is used as the tab icon.' => '空欄にすると、ロゴがタブアイコンとして使われます。',
    'No .ico: a browser handed both an ICO and the SVG Kanboard declares takes the SVG, so an ICO here would never be the icon you see.' => '.ico は使えません：ICO と Kanboard が宣言する SVG の両方を渡されたブラウザは SVG を取るため、ここに置いた ICO が実際のアイコンになることはありません。',

    'Lockup' => 'ロックアップ',
    'Mark only' => 'マークのみ',
    'Mark and name' => 'マークと名前',
    'Mark, name and tagline' => 'マーク、名前、タグライン',
    'Applies to the sidebar, the phone top bar and the login screen. Email always carries the name, whatever is chosen here.' => 'サイドバー、スマートフォンのトップバー、ログイン画面に適用されます。メールはここでの選択にかかわらず常に名前を載せます。',
    'Mark size' => 'マークのサイズ',
    'A percentage of the size the theme draws the mark at. The sidebar beside this screen follows the slider.' => 'テーマが描く既定サイズに対する百分率。この画面の横のサイドバーがスライダーに追従します。',

    /* Upload failures, shown as flash messages. */
    'The image must be smaller than %d KB.' => '画像は %d KB より小さくしてください。',
    'Allowed formats: %s.' => '使用できる形式：%s。',
    'This file is not a valid image.' => 'このファイルは有効な画像ではありません。',
    'This SVG contains script and was refused.' => 'この SVG はスクリプトを含むため拒否されました。',
    'The file could not be uploaded.' => 'ファイルをアップロードできませんでした。',
    'Unknown image.' => '不明な画像です。',

    /* Outgoing mail. */
    'Open the task' => 'タスクを開く',
    'Open the board' => 'ボードを開く',
    'You are receiving this email because you are subscribed to notifications on %s.' => '%s の通知を購読しているため、このメールが送信されています。',
    'This message may contain confidential information. If it was not meant for you, please delete it and do not forward it.' => 'このメッセージには秘密情報が含まれている場合があります。宛先が誤っている場合は、転送せずに削除してください。',

    /* The plugin's own description, on Settings → Plugins. */
    'shadcn/ui design language and HugeIcons for the whole Kanboard interface' => 'Kanboard のインターフェイス全体に shadcn/ui のデザイン言語と HugeIcons を',
    'My project management tool' => '私のプロジェクト管理ツール',
);

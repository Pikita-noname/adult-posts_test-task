<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$title} — {$appName}</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <a href="/">{$appName}</a>
    </header>
    <main>
        {block name="content"}{/block}
    </main>
</body>
</html>

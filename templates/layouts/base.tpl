<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$title} — {$appName}</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <a class="skip-link" href="#content">Перейти к содержимому</a>
    <header class="site-header">
        <div class="site-header__inner">
            <a class="brand" href="/"><span class="brand__mark" aria-hidden="true">a.</span>{$appName}</a>
            <a class="header-link" href="/">Все категории <span aria-hidden="true">↗</span></a>
        </div>
    </header>
    <main id="content" class="container">
        {block name="content"}{/block}
    </main>
    <footer class="site-footer"><div class="container"><a class="brand" href="/">{$appName}</a><p>Истории, которые хочется читать.</p><a href="#content">Наверх ↑</a></div></footer>
</body>
</html>

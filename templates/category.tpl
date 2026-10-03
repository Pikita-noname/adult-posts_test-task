{extends file="layouts/base.tpl"}

{block name="content"}
    <nav class="breadcrumbs" aria-label="Хлебные крошки"><a href="/">Главная</a><span aria-hidden="true">/</span><span aria-current="page">{$category.name}</span></nav>
    <header class="page-heading">
        <p class="eyebrow">Категория</p>
        <h1>{$category.name}</h1>
        <p>{$category.description}</p>
    </header>
    <div class="listing-toolbar">
        <p>Статей: <strong>{$total}</strong></p>
        <form method="get" action="/category/{$category.slug}" class="sort-form">
            <label for="sort">Сортировка</label>
            <select id="sort" name="sort">
                <option value="date" {if $sort === 'date'}selected{/if}>Сначала новые</option>
                <option value="views" {if $sort === 'views'}selected{/if}>По просмотрам</option>
            </select>
            <button class="button button--small" type="submit">Применить</button>
        </form>
    </div>
    <div class="post-grid">
        {foreach $posts as $post}
            {include file="partials/post-card.tpl" post=$post}
        {foreachelse}
            <div class="empty-state"><h2>В этой категории пока нет статей</h2><p>Выберите другую категорию на <a href="/">главной странице</a>.</p></div>
        {/foreach}
    </div>
    {if $pages > 1}
        <nav class="pagination" aria-label="Страницы категории">
            {if $page > 1}<a href="?sort={$sort}&amp;page={$page-1}" aria-label="Предыдущая страница">←</a>{/if}
            {if $pageNumbers[0] > 1}<a href="?sort={$sort}&amp;page=1">1</a>{if $pageNumbers[0] > 2}<span>…</span>{/if}{/if}
            {foreach $pageNumbers as $number}
                {if $number === $page}<span class="is-current" aria-current="page">{$number}</span>{else}<a href="?sort={$sort}&amp;page={$number}">{$number}</a>{/if}
            {/foreach}
            {if $page+2 < $pages}{if $page+3 < $pages}<span>…</span>{/if}<a href="?sort={$sort}&amp;page={$pages}">{$pages}</a>{/if}
            {if $page < $pages}<a href="?sort={$sort}&amp;page={$page+1}" aria-label="Следующая страница">→</a>{/if}
        </nav>
    {/if}
{/block}

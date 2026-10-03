{extends file="layouts/base.tpl"}

{block name="content"}
    <nav class="breadcrumbs" aria-label="Хлебные крошки"><a href="/">Главная</a><span aria-hidden="true">/</span><span aria-current="page">Статья</span></nav>
    <article class="article">
        <header class="article__header">
            <div class="tags">{foreach $categories as $category}<a href="/category/{$category.slug}">{$category.name}</a>{/foreach}</div>
            <h1>{$post.title}</h1>
            <p class="article__lead">{$post.description}</p>
            <div class="meta"><time datetime="{$post.published_at|date_format:'%Y-%m-%d'}">{$post.published_at|date_format:'%d.%m.%Y'}</time><span>Просмотры: {$post.views}</span></div>
        </header>
        <img class="article__image" src="{$post.image}" alt="Иллюстрация к статье «{$post.title}»" width="800" height="500">
        <div class="article__body">{foreach $paragraphs as $paragraph}<p>{$paragraph}</p>{/foreach}</div>
    </article>
    {if $related}
        <section class="related-section" aria-labelledby="related-title">
            <div class="section-heading"><div><p class="eyebrow">Продолжайте читать</p><h2 id="related-title">Похожие статьи</h2></div></div>
            <div class="post-grid">{foreach $related as $item}{include file="partials/post-card.tpl" post=$item}{/foreach}</div>
        </section>
    {/if}
{/block}

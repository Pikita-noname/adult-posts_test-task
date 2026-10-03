{extends file="layouts/base.tpl"}

{block name="content"}
    <section class="hero">
        <p class="eyebrow">Блог об интересном вокруг</p>
        <h1>Истории и идеи<br>на каждый день<span class="accent">.</span></h1>
        <p class="hero__description">Технологии, путешествия, культура и простые радости жизни. Выберите то, что интересно вам.</p>
        {if $categories}
            <nav class="topic-nav" aria-label="Категории">
                {foreach $categories as $category}
                    <a href="/category/{$category.slug}">{$category.name} <span aria-hidden="true">↗</span></a>
                {/foreach}
            </nav>
        {/if}
    </section>

    {foreach $categories as $category}
        <section class="category-section" aria-labelledby="category-{$category.id}">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Последние публикации</p>
                    <h2 id="category-{$category.id}">{$category.name}</h2>
                    <p>{$category.description}</p>
                </div>
                <a class="button button--outline" href="/category/{$category.slug}">Все статьи <span aria-hidden="true">→</span></a>
            </div>
            <div class="post-grid">
                {foreach $category.posts as $post}
                    {include file="partials/post-card.tpl" post=$post}
                {/foreach}
            </div>
        </section>
    {foreachelse}
        <div class="empty-state"><h2>Скоро здесь появятся первые истории</h2><p>Загляните к нам чуть позже.</p></div>
    {/foreach}
{/block}

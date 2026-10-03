<article class="post-card">
    <a class="post-card__image" href="/post/{$post.slug}" tabindex="-1" aria-hidden="true">
        <img src="{$post.image}" alt="" width="800" height="500" loading="lazy">
    </a>
    <div class="post-card__body">
        <div class="meta">
            <time datetime="{$post.published_at|date_format:'%Y-%m-%d'}">{$post.published_at|date_format:'%d.%m.%Y'}</time>
            <span>Просмотры: {$post.views}</span>
        </div>
        <h3><a href="/post/{$post.slug}">{$post.title}</a></h3>
        <p>{$post.description}</p>
        <a class="text-link" href="/post/{$post.slug}" aria-label="Читать: {$post.title}">Читать статью <span aria-hidden="true">↗</span></a>
    </div>
</article>

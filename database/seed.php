<?php

use App\Database\Connection;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$db = null;

try {
    $config = require dirname(__DIR__) . '/bootstrap.php';
    $db = Connection::create($config['db']);
    $categories = [
        ['technology', 'Технологии', 'Инструменты и идеи, которые делают повседневную жизнь удобнее.'],
        ['travel', 'Путешествия', 'Новые маршруты, неспешные прогулки и открытия рядом с домом.'],
        ['culture', 'Культура', 'Книги, архитектура и искусство замечать интересное.'],
        ['lifestyle', 'Образ жизни', 'Простые привычки, уютные пространства и время для себя.'],
    ];
    $topics = [
        'technology' => ['Как устроен современный веб', 'Цифровой порядок без лишних приложений', 'Открытый код в повседневной жизни', 'Почему важны резервные копии', 'Удобные инструменты для заметок', 'Фотография на смартфон', 'Технологии для спокойной работы', 'Как выбрать первый язык программирования', 'Домашняя библиотека в цифровом формате', 'Маленькие проекты для большого опыта', 'Доступность начинается с деталей', 'Как защитить личные данные'],
        'travel' => ['Выходные в незнакомом городе', 'Маршрут вдоль побережья', 'Путешествие с одним рюкзаком', 'Как найти тихие места', 'Город глазами местных жителей', 'Поездка на поезде', 'Прогулка без заранее выбранного маршрута', 'Что взять в короткий поход', 'Дневник путешествий', 'Горы на рассвете', 'Осенний маршрут по паркам', 'Искусство медленного путешествия'],
        'culture' => ['Как смотреть на архитектуру', 'Книга для неспешного вечера', 'Маленькие музеи большого города', 'Зачем вести читательский дневник', 'Искусство в общественных пространствах', 'Музыка как часть города', 'История одной фотографии', 'Современная керамика', 'Прогулка по старым кварталам', 'Как устроена выставка', 'Типографика вокруг нас', 'Традиции и современный дизайн'],
        'lifestyle' => ['Утро без спешки', 'Рабочее место у окна', 'Как начать гулять каждый день', 'Простые блюда выходного дня', 'Растения в городской квартире', 'Время без уведомлений', 'Порядок на книжной полке', 'Небольшие привычки для отдыха', 'Домашний кофе как ритуал', 'Планирование без перегрузки', 'Вечерняя прогулка', 'Почему полезно пробовать новое'],
    ];

    $db->beginTransaction();
    // Existing content and view counters are preserved on subsequent runs.
    $insertCategory = $db->prepare(
        'INSERT INTO categories (slug, name, description) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)'
    );
    $categoryIds = [];
    foreach ($categories as [$slug, $name, $description]) {
        $insertCategory->execute([$slug, $name, $description]);
        $categoryIds[$slug] = (int) $db->lastInsertId();
    }

    $insertPost = $db->prepare(
        'INSERT INTO posts (slug, title, image, description, body, views, published_at)
         VALUES (?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)'
    );
    $link = $db->prepare(
        'INSERT INTO post_category (post_id, category_id) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE post_id = post_id'
    );
    $now = new DateTimeImmutable('today', new DateTimeZone('UTC'));
    foreach ($categories as $categoryIndex => [$slug]) {
        foreach ($topics[$slug] as $index => $title) {
            $description = $title . ': наблюдения, практические советы и идеи, которые можно попробовать уже сегодня.';
            $body = $description . "\n\n"
                . 'Начните с небольшого шага. Выберите то, что вам интересно, выделите немного времени и попробуйте разобраться в деталях. Личный опыт помогает понять больше, чем длинный список чужих рекомендаций.' . "\n\n"
                . 'Обращайте внимание на контекст. То, что подходит одному человеку, не обязательно подойдёт другому. Записывайте наблюдения, сравнивайте варианты и оставляйте место для экспериментов.' . "\n\n"
                . 'Не стремитесь сделать всё за один день. Вернитесь к своему опыту через неделю: что оказалось полезным, что хочется изменить и чем можно поделиться с друзьями? Именно из таких маленьких открытий складываются устойчивые привычки.';
            $insertPost->execute([
                'demo-' . $slug . '-' . ($index + 1), $title,
                '/assets/images/' . $slug . '.svg', $description, $body,
                (($index + 1) * 137 + $categoryIndex * 53) % 1800,
                $now->modify('-' . ($index * 4 + $categoryIndex) . ' days')->format('Y-m-d H:i:s'),
            ]);
            $postId = (int) $db->lastInsertId();
            $link->execute([$postId, $categoryIds[$slug]]);
            if ($index % 4 === 0) {
                $nextSlug = $categories[($categoryIndex + 1) % count($categories)][0];
                $link->execute([$postId, $categoryIds[$nextSlug]]);
            }
        }
    }
    $db->commit();
    echo "Demo content ready: 4 categories, 48 posts. Existing content preserved.\n";
} catch (Throwable $exception) {
    if ($db?->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}

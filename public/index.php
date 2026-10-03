<?php

use App\Http\Application;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = new Application(dirname(__DIR__));
$app->run();

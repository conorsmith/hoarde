<?php
declare(strict_types=1);

header("Content-Type: text/js");

$controllers = scandir(__DIR__ . "/../src/Frontend/Js/Controller");

foreach ($controllers as $controller) {
    include __DIR__ . "/../src/Frontend/Js/Controller/{$controller}";
}

$models = scandir(__DIR__ . "/../src/Frontend/Js/Model");

foreach ($models as $model) {
    include __DIR__ . "/../src/Frontend/Js/Model/{$model}";
}

$views = scandir(__DIR__ . "/../src/Frontend/Js/View");

foreach ($views as $view) {
    include __DIR__ . "/../src/Frontend/Js/View/{$view}";
}

include __DIR__ . "/../src/Frontend/Js/classes.js";
include __DIR__ . "/../src/Frontend/Js/main.js";

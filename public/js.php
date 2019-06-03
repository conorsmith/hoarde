<?php
declare(strict_types=1);

header("Content-Type: text/js");

$controllers = scandir(__DIR__ . "/../src/Frontend/Js/Controller");

foreach ($controllers as $controller) {
    if (!in_array($controller, [".", ".."])) {
        include __DIR__ . "/../src/Frontend/Js/Controller/{$controller}";
    }
}

$models = scandir(__DIR__ . "/../src/Frontend/Js/Model");

foreach ($models as $model) {
    if (!in_array($model, [".", ".."])) {
        include __DIR__ . "/../src/Frontend/Js/Model/{$model}";
    }
}

$views = scandir(__DIR__ . "/../src/Frontend/Js/View");

foreach ($views as $view) {
    if (!in_array($view, [".", ".."])) {
        include __DIR__ . "/../src/Frontend/Js/View/{$view}";
    }
}

include __DIR__ . "/../src/Frontend/Js/classes.js";
include __DIR__ . "/../src/Frontend/Js/main.js";

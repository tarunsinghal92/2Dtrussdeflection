<?php

    // Include the Router class
    require_once __DIR__ . '/includes/autoloader.inc';

    // Create a Router
    $router = new \Bramus\Router\Router();

    // Custom 404 Handler
    $router->set404(function () {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        echo '404, route not found!';
    });

    // Static route: /
    $router->get('/(\w*)', function () {

        // call the tempate
        $main = new MainController();
        $main->show_template();
    });

    // Thunderbirds are go!
    $router->run();

// EOF

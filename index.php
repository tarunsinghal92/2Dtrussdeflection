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
        // // call the tempate
        // $modelling = new ModellingController($project_id, $module, $step);
        // $modelling->show_template();
        echo "TKS";
    });

    // Thunderbirds are go!
    $router->run();

// EOF

<?php

/**
 * Main Controller Class
 */

class MainController extends Common{

    //all results
    private $results;

    //default function
    public function __construct(){

      //get the results
      $this->results = new TrussModel();
      $this->results->run();
    }

    public function show_template(){

        //show template
        require_once 'views/main.phtml';
    }
}

?>

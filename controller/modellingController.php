<?php

/**
 * Modelling Controller Class
 */

class ModellingController extends Common{

    //private variables
    private $projectID;
    private $module;
    private $step;
    private $projectDetails;
    private $entityDatabase;
    private $componentDatabase;

    //default function
    public function __construct($projectID = NULL, $module = NULL, $step = NULL){

      //set variables
      $this->module = $module;
      $this->step = $step;
      $this->projectID = $projectID;

      //get projects
      $projectsObj = new ProjectDatabase(" id = $this->projectID");
      $this->projectDetails = $projectsObj->getAllProjects();

      //get components
      $componentObj = new ComponentDatabase();
      $this->componentDatabase = $componentObj->getAllComponents();

      //get entities
      $entityObj = new EntityDatabase();
      $this->entityDatabase = $entityObj->getAllEntities();
    }

    public function show_template(){

        //show template
        require_once 'views/main.phtml';
    }


}

?>

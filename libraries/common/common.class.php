<?php

/**
 * Common functions Class
 */

class Common{

  /**
   *
   * Pass a string through some filters for security purposes
   *
   * @param string $s The string to be secured
   * @return string The secured string
   *
   */
  public function secure_string($s){

      // Pass it through a list of string cleaning functions
      $s = htmlentities($s);
      $s = strip_tags($s);
      $s = utf8_decode($s);
      $s = htmlspecialchars($s);
      $s = stripslashes($s);
      $s = preg_replace( '/[^[:print:]]+/', '', trim($s)); //remove non printable characters

      // Trim to a certain length for security purposes
      $s = substr($s, 0, 100);

      //return
      return $s;
  }

  /**
  *
  * print full array
  */
  public function dump($data){
    echo "<pre>";
    print_r($data);
    echo "</pre>";
  }

  /**
   * [getParam description]
   * @param  [type] $param [description]
   * @return [type]        [description]
   */
  public function getParam($param){
    if(isset($this->$param)){
      return $this->$param;
    }
    return NULL;
  }

}

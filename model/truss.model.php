<?php

/**
 * Component Model Class
 * |_ Structure Component
 * |_ Non - Structure Component
 */

class ComponentDatabase extends Common
{

    /**
   * private variables
   */
    public $componentID;
    public $name;
    public $description;
    public $type;
    public $fragilityCurveID;
    public $materialPropertiesID;
    public $Entity;
    public $isDeleted = false;
    public $dateCreated;

    /**
   * [getComponent make component object]
   *
   * @param  [array] $data
   * @return [object]
   */
    public function getComponent($data)
    {

        //set var
        $this->componentID          = $data['id'];
        $this->name                 = $data['name'];
        $this->description          = $data['description'];
        $this->type                 = $data['type'];
        $this->isDeleted            = $data['is_deleted'];
        $this->fragilityCurveID     = $data['fragility_curve_id'];
        $this->materialPropertiesID = $data['material_properties_id'];
        $this->Entity               = $data['structure_entity_id'];

        //return
        return $this;
    }

    public function getFragilityCurve()
    {

        //get data
        $fragilityObj = new FragilityDatabase($this->fragilityCurveID);
        $fragilityObj->getData();

        //return
        return $fragilityObj;
    }

    /**
   * [getMaterialProperties description]
   *
   * @return [type] [description]
   */
    public function getMaterialProperties()
    {

        //get data
        $materialObj = new MaterialDatabase($this->materialPropertiesID);
        $materialObj->getProperties();

        //return
        return $materialObj;
    }

    /**
   * [getAllComponents description]
   *
   * @return [type] [description]
   */
    public function getAllComponents($condition = "1")
    {

        //db connection
        global $db;

        //get all components
        $db->query("SELECT * FROM components WHERE $condition");
        $allComponents = $db->resultset();

        //loop & make object
        $allComponentsObj = array();
        foreach ($allComponents as $key => $value) {

            //form obj and store
            $tmp = new ComponentDatabase();
            $allComponentsObj[] = $tmp->getComponent($value);
        }

        //return
        return $allComponentsObj;
    }

}

?>

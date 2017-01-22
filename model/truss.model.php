<?php

/**
 * Truss Model Class
 *
 * K = (EA/L) * [ cˆ2 c*s -cˆ2 -c*s ;
 *               c*s sˆ2 -c*s -sˆ2 ;
 *               -cˆ2 -c*s cˆ2 c*s ;
 *               -c*s -sˆ2 c*s sˆ2 ];
 */

use MathPHP\LinearAlgebra\Matrix;
use MathPHP\LinearAlgebra\MatrixFactory;
use MathPHP\LinearAlgebra\Vector;

class TrussModel extends Common
{

    private $force_file = "data/forces.txt";
    private $members_file = "data/members.txt";
    private $forces = [];
    public $nodes = [];
    public $elements = [];
    private $stiffness_matrix = [];
    private $modified_stiffness_matrix = [];
    private $displacements = [];

    public function run()
    {

        //get nodes and elements
        $this->get_geometry();

        //get force vector
        $this->get_forces();

        //get the stiffness matrix
        $this->get_stiffness_matrix();

        //calculate displacement
        $this->get_displacements();

        //get modified nodes / elements
        $this->get_modified_geometry();

        //debug
        // $this->dump($this->nodes);
        // $this->dump($this->elements);
        // $this->show($this->modified_stiffness_matrix);
        // $this->dump($this->displacements);
        // die;
    }

    public function get_modified_geometry()
    {
        // for nodes
        foreach ($this->nodes as $key => $node) {
            $this->nodes[$key]['mposx'] = $node['posx'] + ($this->displacements[2 * ($key - 1) + 0] * MAGNIFICATION_FACTOR);
            $this->nodes[$key]['mposy'] = $node['posy'] + ($this->displacements[2 * ($key - 1) + 1] * MAGNIFICATION_FACTOR);
        }

        // for elements
        foreach ($this->elements as $key => $element) {

            $n1 = intval(@current(explode('-', $key)));
            $n2 = intval(@end(explode('-', $key)));
            $this->elements[$key]['mposx1'] = $element['posx1'] + ($this->displacements[2 * ($n1 - 1) + 0] * MAGNIFICATION_FACTOR);
            $this->elements[$key]['mposy1'] = $element['posy1'] + ($this->displacements[2 * ($n1 - 1) + 1] * MAGNIFICATION_FACTOR);
            $this->elements[$key]['mposx2'] = $element['posx2'] + ($this->displacements[2 * ($n2 - 1) + 0] * MAGNIFICATION_FACTOR);
            $this->elements[$key]['mposy2'] = $element['posy2'] + ($this->displacements[2 * ($n2 - 1) + 1] * MAGNIFICATION_FACTOR);
        }
    }

    public function get_displacements()
    {
        //solve f = kx
        $Ainv  = $this->modified_stiffness_matrix->inverse();
        $d = $Ainv->vectorMultiply($this->forces);

        // get zero displacements
        $this->displacements = [];
        $scnt = 0;
        foreach ($this->nodes as $key => $node) {
            if($node['xtype'] == 'fixed'){
                $this->displacements[2 * ($key - 1) + 0] = 0;
            }else{
                $this->displacements[2 * ($key - 1) + 0] = $d->get($scnt);
                $scnt++;
            }
            if($node['ytype'] == 'fixed'){
                $this->displacements[2 * ($key - 1) + 1] = 0;
            }else{
                $this->displacements[2 * ($key - 1) + 1] = $d->get($scnt);
                $scnt++;
            }
        }
    }

    public function get_stiffness_matrix()
    {
        // initialize stiffness matrix
        $stiffness_matrix = $this->initialize_matrix(2 * count($this->nodes));

        foreach ($this->elements as $member => $element) {

            //add member to matrix
            $stiffness_matrix = $this->add_member_stiffness_matrix($stiffness_matrix, $member, $element);
        }

        //store
        $this->stiffness_matrix = MatrixFactory::create($stiffness_matrix);
        $this->modified_stiffness_matrix = clone $this->stiffness_matrix;

        // remove fixed DOFs
        $r_cnt = 0;
        foreach ($this->nodes as $key => $node) {

            if($node['xtype'] == 'fixed'){
                $this->modified_stiffness_matrix = $this->modified_stiffness_matrix->columnExclude(2 * ($key - 1) + 0 - $r_cnt);
                $this->modified_stiffness_matrix = $this->modified_stiffness_matrix->rowExclude(2 * ($key - 1) + 0 - $r_cnt);
                $r_cnt++;
            }
            if($node['ytype'] == 'fixed'){
                $this->modified_stiffness_matrix = $this->modified_stiffness_matrix->columnExclude(2 * ($key - 1) + 1 - $r_cnt);
                $this->modified_stiffness_matrix = $this->modified_stiffness_matrix->rowExclude(2 * ($key - 1) + 1 - $r_cnt);
                $r_cnt++;
            }
        }
    }

    public function add_member_stiffness_matrix($stiffness_matrix, $member, $element)
    {
        // var
        $n1 = intval(@current(explode('-', $member)));
        $n2 = intval(@end(explode('-', $member)));
        $length = $this->get_length($element, 12);
        $theta = $this->get_theta($element);
        $EA_L = (E * A )/ $length;

        //dof
        $dof1x = 2 * ($n1 - 1) + 0;
        $dof1y = 2 * ($n1 - 1) + 1;
        $dof2x = 2 * ($n2 - 1) + 0;
        $dof2y = 2 * ($n2 - 1) + 1;

        //1st row
        $stiffness_matrix[$dof1x][$dof1x] += round($EA_L * cos($theta) * cos($theta), 5);
        $stiffness_matrix[$dof1y][$dof1x] += round($EA_L * cos($theta) * sin($theta), 5);
        $stiffness_matrix[$dof2x][$dof1x] += round($EA_L * -cos($theta) * cos($theta), 5);
        $stiffness_matrix[$dof2y][$dof1x] += round($EA_L * -cos($theta) * sin($theta), 5);

        //2nd row
        $stiffness_matrix[$dof1x][$dof1y] += round($EA_L * cos($theta) * sin($theta), 5);
        $stiffness_matrix[$dof1y][$dof1y] += round($EA_L * sin($theta) * sin($theta), 5);
        $stiffness_matrix[$dof2x][$dof1y] += round($EA_L * -cos($theta) * sin($theta), 5);
        $stiffness_matrix[$dof2y][$dof1y] += round($EA_L * -sin($theta) * sin($theta), 5);

        //3rd row
        $stiffness_matrix[$dof1x][$dof2x] += round($EA_L * -cos($theta) * cos($theta), 5);
        $stiffness_matrix[$dof1y][$dof2x] += round($EA_L * -cos($theta) * sin($theta), 5);
        $stiffness_matrix[$dof2x][$dof2x] += round($EA_L * cos($theta) * cos($theta), 5);
        $stiffness_matrix[$dof2y][$dof2x] += round($EA_L * cos($theta) * sin($theta), 5);

        //4th row
        $stiffness_matrix[$dof1x][$dof2y] += round($EA_L * -cos($theta) * sin($theta), 5);
        $stiffness_matrix[$dof1y][$dof2y] += round($EA_L * -sin($theta) * sin($theta), 5);
        $stiffness_matrix[$dof2x][$dof2y] += round($EA_L * cos($theta) * sin($theta), 5);
        $stiffness_matrix[$dof2y][$dof2y] += round($EA_L * sin($theta) * sin($theta), 5);

        //return
        return $stiffness_matrix;
    }

    public function show($matrix)
    {
        echo '<pre>';
        print($matrix);
        echo '</pre>';
    }

    public function get_length($element, $factor = 1)
    {
        return $factor * sqrt((($element['posy2'] - $element['posy1']) * ($element['posy2'] - $element['posy1']) + ($element['posx2'] - $element['posx1']) * ($element['posx2'] - $element['posx1'])));
    }

    public function get_theta($element)
    {
        if(abs($element['posx2'] - $element['posx1']) == 0){
            return pi()/2;
        }else{
            return atan(($element['posy2'] - $element['posy1']) / ($element['posx2'] - $element['posx1']));
        }
    }

    public function initialize_matrix($n)
    {
        $t = array_fill(0, $n, 0.0);
        return array_fill(0, $n, $t);
    }

    public function get_geometry()
    {
        //define
        $nodes = [];
        $elements = [];

        //get data from files
        $handle = fopen($this->members_file, "r");

        $i = 0;
        while (($line = fgets($handle)) !== false) {
            // process the line read.
            if($i != 0){
                $line = preg_split('/\s+/', $line);

                // nodes
                $nodes[floatval(@current(explode('-', $line[0])))] = [
                  'posx'=> floatval(@current(explode(',', $line[1]))),
                  'posy'=> floatval(@end(explode(',', $line[1]))),
                  'xtype'=> @current(explode(',', $line[3])),
                  'ytype'=> @end(explode(',', $line[3]))
                ];
                $nodes[floatval(@end(explode('-', $line[0])))] = [
                  'posx'=> floatval(@current(explode(',', $line[2]))),
                  'posy'=> floatval(@end(explode(',', $line[2]))),
                  'xtype'=> @current(explode(',', $line[4])),
                  'ytype'=> @end(explode(',', $line[4]))
                ];

                //elements
                $elements[$line[0]] = [
                  'posx1'=> floatval(@current(explode(',', $line[1]))),
                  'posy1'=> floatval(@end(explode(',', $line[1]))),
                  'posx2'=> floatval(@current(explode(',', $line[2]))),
                  'posy2'=> floatval(@end(explode(',', $line[2])))
                ];
            }
            $i++;
        }

        //close file
        fclose($handle);

        //store
        ksort($nodes);
        $this->nodes = $nodes;
        $this->elements = $elements;
    }

    /**
     * @return matrix of forces
     */
    public function get_forces()
    {
        //define vector
        $force_matrix = [];

        //get data from files
        $handle = fopen($this->force_file, "r");

        $i = 0;
        while (($line = fgets($handle)) !== false) {
            // process the line read.
            if($i != 0){
                $line = preg_split('/\s+/', $line);
                $force_matrix[(2 * (intval($line[0]) - 1) + 0)] = $line[1];
                $force_matrix[(2 * (intval($line[0]) - 1) + 1)] = $line[2];
            }
            $i++;
        }

        //close file
        fclose($handle);
        ksort($force_matrix);

        //store
        $this->forces = MatrixFactory::create($force_matrix);

        // remove fixed DOFs
        $r_cnt = 0;
        foreach ($this->nodes as $key => $node) {
            if($node['xtype'] == 'fixed'){
                $this->forces = $this->forces->rowExclude(2 * ($key - 1) + 0 - $r_cnt);
                $this->forces = $this->forces->columnExclude(2 * ($key - 1) + 0 - $r_cnt);
                $r_cnt++;
            }
            if($node['ytype'] == 'fixed'){
                $this->forces = $this->forces->rowExclude(2 * ($key - 1) + 1 - $r_cnt);
                $this->forces = $this->forces->columnExclude(2 * ($key - 1) + 1 - $r_cnt);
                $r_cnt++;
            }
        }
        // store
        $this->forces = new Vector($this->forces->getDiagonalElements());
    }


}

?>

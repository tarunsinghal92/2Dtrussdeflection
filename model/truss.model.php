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
    public $legend = [];
    public $max = ['member_force' => 0, 'displacement' => 0];

    public function __construct()
    {
        $this->legend = [
          ['startval' => -1000000,'endval' => -300000,'color' => $this->blend_hex('ff0000', '00ff00', 0.0)],
          ['startval' => -300000,'endval' => -100000,'color' => $this->blend_hex('ff0000', '00ff00', 0.08)],
          ['startval' => -100000,'endval' => -50000,'color' => $this->blend_hex('ff0000', '00ff00', 0.16)],
          ['startval' => -50000,'endval' => -10000,'color' => $this->blend_hex('ff0000', '00ff00', 0.24)],
          ['startval' => -10000,'endval' => -5000,'color' => $this->blend_hex('ff0000', '00ff00', 0.32)],
          ['startval' => -5000,'endval' => -0.1,'color' => $this->blend_hex('ff0000', '00ff00', 0.40)],
          ['startval' => -0.1,'endval' => 0.1,'color' => $this->blend_hex('ff0000', '00ff00', 0.48)],
          ['startval' => 0.1,'endval' => 5000,'color' => $this->blend_hex('ff0000', '00ff00', 0.57)],
          ['startval' => 5000,'endval' => 10000,'color' => $this->blend_hex('ff0000', '00ff00', 0.65)],
          ['startval' => 10000,'endval' => 50000,'color' => $this->blend_hex('ff0000', '00ff00', 0.74)],
          ['startval' => 50000,'endval' => 100000,'color' => $this->blend_hex('ff0000', '00ff00', 0.82)],
          ['startval' => 100000,'endval' => 300000,'color' => $this->blend_hex('ff0000', '00ff00', 0.9)],
          ['startval' => 300000,'endval' => 1000000,'color' => $this->blend_hex('ff0000', '00ff00', 1)],
        ];
    }

    public function run()
    {

        // get nodes and elements
        $this->get_geometry();

        // get force vector
        $this->get_forces();

        // get the stiffness matrix
        $this->get_stiffness_matrix();

        // calculate displacement
        $this->get_displacements();

        // get modified nodes / elements
        $this->get_modified_geometry();

        // debug
        // $this->dump($this->nodes);
        // $this->dump(min($this->displacements) * 25.4);
        // $this->dump($this->elements);
        // $this->dump($this->max);
        // die;
    }

    private function blend_hex($from, $to, $pos = 0.5)
    {
        // 1. Grab RGB from each colour
        list($fr, $fg, $fb) = sscanf($from, '%2x%2x%2x');
        list($tr, $tg, $tb) = sscanf($to, '%2x%2x%2x');

        // 2. Calculate colour based on frational position
        $r = (int) ($fr - (($fr - $tr) * $pos));
        $g = (int) ($fg - (($fg - $tg) * $pos));
        $b = (int) ($fb - (($fb - $tb) * $pos));

        // 3. Format to 6-char HEX colour string
        return sprintf('#%02x%02x%02x', $r, $g, $b);
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

        //MAGNIFICATION_FACTOR FOR OVERALL STRUCTURE
        foreach ($this->nodes as $key => $node) {
            $this->nodes[$key]['posx'] = $node['posx'] * GENERAL_MAGNIFICATION_FACTOR;
            $this->nodes[$key]['posy'] = $node['posy'] * GENERAL_MAGNIFICATION_FACTOR;
            $this->nodes[$key]['mposx'] = $node['mposx'] * GENERAL_MAGNIFICATION_FACTOR;
            $this->nodes[$key]['mposy'] = $node['mposy'] * GENERAL_MAGNIFICATION_FACTOR;
        }
        foreach ($this->elements as $key => $element) {
            $this->elements[$key]['posx1'] = $element['posx1'] * GENERAL_MAGNIFICATION_FACTOR;
            $this->elements[$key]['posy1'] = $element['posy1'] * GENERAL_MAGNIFICATION_FACTOR;
            $this->elements[$key]['mposx1'] = $element['mposx1'] * GENERAL_MAGNIFICATION_FACTOR;
            $this->elements[$key]['mposy1'] = $element['mposy1'] * GENERAL_MAGNIFICATION_FACTOR;
            $this->elements[$key]['posx2'] = $element['posx2'] * GENERAL_MAGNIFICATION_FACTOR;
            $this->elements[$key]['posy2'] = $element['posy2'] * GENERAL_MAGNIFICATION_FACTOR;
            $this->elements[$key]['mposx2'] = $element['mposx2'] * GENERAL_MAGNIFICATION_FACTOR;
            $this->elements[$key]['mposy2'] = $element['mposy2'] * GENERAL_MAGNIFICATION_FACTOR;
        }

        // check compression or tension
        $legend = [];
        foreach ($this->elements as $key => $element) {
            $orig = $this->get_length($element, LENGTH_FACTOR, 'pos');
            $mod = $this->get_length($element, LENGTH_FACTOR, 'mpos');
            $this->elements[$key]['forces'] = intval($element['area'] * E * (($mod - $orig) / $orig));
            $this->elements[$key]['stress'] = intval(E * (($mod - $orig) / $orig));
            $this->elements[$key]['type'] = $orig - $mod > 0 ? 'compression': 'tension';
            $this->elements[$key]['color'] = $this->get_color($this->elements[$key]['forces']);
            $legend[] = $this->elements[$key]['forces'];
            $this->max['member_force'] = intval(max($this->max['member_force'], abs($this->elements[$key]['forces']))) . ' lbs';
        }
    }

    private function get_color($val)
    {
        foreach ($this->legend as $key => $v) {
            if($val > $v['startval'] && $val < $v['endval']){
                return $v['color'];
            }
        }
        return '#ffffff';
    }

    public function get_displacements()
    {
        //solve k^-1*f = x
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

        //store max displacement
        $this->max['displacement'] = intval(min($this->displacements)) . ' in';
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
        $length = $this->get_length($element, LENGTH_FACTOR);
        $theta = $this->get_theta($element);
        $EA_L = (E * $element['area'] )/ $length;

        //dof
        $dof1x = 2 * ($n1 - 1) + 0;
        $dof1y = 2 * ($n1 - 1) + 1;
        $dof2x = 2 * ($n2 - 1) + 0;
        $dof2y = 2 * ($n2 - 1) + 1;

        //1st row
        $stiffness_matrix[$dof1x][$dof1x] += ($EA_L * cos($theta) * cos($theta));
        $stiffness_matrix[$dof1y][$dof1x] += ($EA_L * cos($theta) * sin($theta));
        $stiffness_matrix[$dof2x][$dof1x] += ($EA_L * -cos($theta) * cos($theta));
        $stiffness_matrix[$dof2y][$dof1x] += ($EA_L * -cos($theta) * sin($theta));

        //2nd row
        $stiffness_matrix[$dof1x][$dof1y] += ($EA_L * cos($theta) * sin($theta));
        $stiffness_matrix[$dof1y][$dof1y] += ($EA_L * sin($theta) * sin($theta));
        $stiffness_matrix[$dof2x][$dof1y] += ($EA_L * -cos($theta) * sin($theta));
        $stiffness_matrix[$dof2y][$dof1y] += ($EA_L * -sin($theta) * sin($theta));

        //3rd row
        $stiffness_matrix[$dof1x][$dof2x] += ($EA_L * -cos($theta) * cos($theta));
        $stiffness_matrix[$dof1y][$dof2x] += ($EA_L * -cos($theta) * sin($theta));
        $stiffness_matrix[$dof2x][$dof2x] += ($EA_L * cos($theta) * cos($theta));
        $stiffness_matrix[$dof2y][$dof2x] += ($EA_L * cos($theta) * sin($theta));

        //4th row
        $stiffness_matrix[$dof1x][$dof2y] += ($EA_L * -cos($theta) * sin($theta));
        $stiffness_matrix[$dof1y][$dof2y] += ($EA_L * -sin($theta) * sin($theta));
        $stiffness_matrix[$dof2x][$dof2y] += ($EA_L * cos($theta) * sin($theta));
        $stiffness_matrix[$dof2y][$dof2y] += ($EA_L * sin($theta) * sin($theta));

        //return
        return $stiffness_matrix;
    }

    public function show($matrix)
    {
        echo '<pre>';
        print($matrix);
        echo '</pre>';
    }

    public function get_length($element, $factor = 1, $pre = 'pos')
    {
        return $factor * sqrt((($element[$pre.'y2'] - $element[$pre.'y1']) * ($element[$pre.'y2'] - $element[$pre.'y1']) + ($element[$pre.'x2'] - $element[$pre.'x1']) * ($element[$pre.'x2'] - $element[$pre.'x1'])));
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
                  'posy2'=> floatval(@end(explode(',', $line[2]))),
                  'area'=> floatval($line[5])
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

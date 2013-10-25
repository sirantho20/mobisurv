<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GateWay
 *
 * @author tony
 */
class GateWay {
    
    public function __construct() 
    {
        $record = $_POST['name'];
        echo 'Welcome, '.$record;
    }
}

$gt = new GateWay();

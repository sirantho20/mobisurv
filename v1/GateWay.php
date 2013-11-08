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

if(isset($_POST))
{
    $action = filter_input(INPUT_POST, 'action');
    
    switch ($action)
    {
        case 'data_upload':
            echo 'I got this data: '.$_POST['value'][0];
            break;
    }
}

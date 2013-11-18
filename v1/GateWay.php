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
    include '../remote/libraries/MobiSyncRemote.php';
    
    $obj = new MobiSyncRemote();
    
    $action = filter_input(INPUT_POST, 'action');
    
    switch ($action)
    {
        case 'data_upload':
            $data = $_POST['value'][0];
            if( $obj->updateRemoteData($data))
            {
                
              echo 'success';
              
            }
            else 
            {
                echo $obj->err;
            }
            
            break;
        
        case 'get_update':
            
            //echo $obj->getRemoteData();
            //$ignore = '--ignore-table=lime.users';
            passthru('mysqldump -uroot -pAFtony19833 --skip-comments lime');
            break;
        
        case 'request_source_update':
            echo 'http://localhost/mobisurv.tar';
            break;
        
        default :
            break;
    }
}
else 
{
    echo 'invalid request';
}

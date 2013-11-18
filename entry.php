<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
ini_set('max_execution_time', 120);
require 'libraries/MobiSync.php';

if(isset($_GET))
{
    $action = filter_input(INPUT_GET, 'action');
    $obj = new MobiSync();
    switch ($action)
    {
        case 'update':
            
            if( $obj->getRemoteUpdate() )
            {
                $obj->core_object->sourceUpdate();
                 echo 'Update completed successfully';
            }
            else 
            {
                echo $obj->err;
            }
            break;
            
        case 'push':
            
            $sid = filter_input(INPUT_GET, 'sid');
            
            if( $obj->moveData($sid) )
            {
                //print_r($re);
                echo 'Data successfully published to server';
            }
            else 
            {
                echo $obj->err;
            }
            
            
    }
}

<?php
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
                 echo 'Update completed successfully';
            }
            else 
            {
                echo $obj->err;
            }
            break;
            
        case 'push':
            
            if( $obj->moveData() )
            {
                
                echo 'Data successfully published to server';
            }
            else 
            {
                echo $obj->err;
            }
            
            
    }
}

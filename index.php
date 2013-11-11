<?php
ini_set('max_execution_time', 300);
require 'libraries/MobiSync.php';

if(isset($_GET))
{
    $action = filter_input(INPUT_GET, 'action');
    
    switch ($action)
    {
        case 'update':
            $obj = new MobiSync();
            if($obj->getRemoteUpdate())
            {
                echo 'Successful';
            }
            else 
            {
                echo $obj->err;
            }
            
    }
}

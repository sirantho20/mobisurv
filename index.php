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
            if($re = $obj->getRemoteUpdate())
            {
                var_dump($re);
            }
            else 
            {
                echo $obj->err;
            }
            
    }
}

<?php

require 'libraries/MobiSync.php';

if(isset($_GET))
{
    $action = filter_input(INPUT_GET, 'action');
    
    switch ($action)
    {
        case 'update':
            $obj = new MobiSync();
            echo $obj->getRemoteUpdate();
            
    }
}

<?php
/**
 * Class responsible for synchronising local and remote database. Depends on MobiCore for settings.
 * 
 * @author Softcube Limited <info@softcube.co>
 * @link http://softcube.co Softcube
 * @copyright (c) 2013, All Rights Reserved
 * @version 1.0
 */
ini_set('display_startup_errors', 1);
include 'MobiCore.php';

class MobiSyncRemote
{

    public $local_db_instance;
    public $remote_db_instance;
    public $terminal_email;
    public $core_object;
    public $err;
    public function __construct()
    {
        $this->core_object = new MobiCore();
        $this->local_db_instance = $this->createInstance( 'local' );
        //$this->remote_db_instance = $this->createInstance( 'remote' );
        var_dump($this->getRemoteData());
    }
    
    /**
     * Establish a PDO database connection
     * @param string $location Either 'local' or 'remote', indicating whether it is connecting to a local database instance or a remote one
     * 
     * @return object returns a PDO database connection instance
     */
    private function createInstance( $location )
    {
        $instance = '';
        $core_obj = $this->core_object;
        
        switch ( $location )
        {
            case 'local':
                try
                {
                    $db_type = 'mysql';
                    $instance = new PDO(
                        $db_type.':dbname='.$core_obj->local_db_name.';host='.$core_obj->local_db_host, 
                        $core_obj->local_db_user, 
                        $core_obj->local_db_password,
                        array( PDO::ATTR_PERSISTENT => true ));
                
                } 
                catch (Exception $ex) 
                {
                    print 'Local Database Connection error: '.$ex->getMessage();
                    die();
                }
                
                break;
                
            case 'remote':
                try 
                {
                    $instance = new PDO(
                        $core_obj->remote_db_type.':dbname='.$core_obj->remote_db_name.';host='.$core_obj->remote_db_host, 
                        $core_obj->remote_db_user, 
                        $core_obj->remote_db_password,
                        array( PDO::ATTR_PERSISTENT => true ));
                } 
                catch (Exception $ex) 
                {
                    print 'Remote Database Connection error: '.$ex->getMessage();
                    die();
                }
                
                break;

                
        }
        
        return $instance;
    }
    
    /**
     * Gets all local survey answer records to uploaded to remote serer
     * 
     * @return string Returns a string of SQL insert statements for transmission to remote server
     */
    public function getRemoteData()
    {
        $tables = array();
        $survey_tables = array();
        $output = '';
        // Grab all active survey ids
        $db = $this->local_db_instance;
        $prep = $db->prepare( 'show tables' );
        $prep->execute();
        while ( $records = $prep->fetch(PDO::FETCH_BOTH, PDO::FETCH_ORI_NEXT) )
        {
            $tables[] = $records[0];
        }
        
        
        
        // Extract table data
        foreach ( $tables as $table )
        {
            
            $query = 'select * from '.$table;
            $qr = $db->prepare($query);
            $qr->execute();
            $col_count = $qr->columnCount();
            $row_count = $qr->rowCount();
            
            if ( $row_count > 0 )
            {
                $table_data = 'INSERT INTO '.$table.' VALUES ';
                $counter = 0;
                
                while ( $row = $qr->fetch( PDO::FETCH_BOTH, PDO::FETCH_ORI_NEXT ) )
                {
                    $row_data = '(';

                    for( $i=0; $i < $col_count; $i++ )
                    {
                        // End of row columns
                        $end = $col_count - 1;
                        if($i != $end)
                        {
                            if( isset( $row[$i] ) )
                            {
                                $val = (int)$row[$i];
                                if($val)
                                {
                                    switch ( $i )
                                    {
                                        case 0:
                                            $row_data .= 'NULL, ';
                                            break;
                                        
                                        case 2:
                                           $row_data .= '"'.$row[$i].'", ';
                                           break;
                                       default :
                                           $row_data .= $row[$i].', ';
                                    }
                                }
                                else 
                                {
                                    $row_data .= $i != 0? '"'.$row[$i].'", ':'NULL, ';
                                }
                            }
                            else 
                            {
                                $row_data .= 'NULL, ';
                            }
                        }

                        else 
                        {
                            // Last column in a row
                            if( $i != $counter )
                            {
                                if( isset( $row[$i] ) )
                                {
                                    $val = (int)$row[$i];
                                    if($val)
                                    {
                                        $row_data .= $row[$i].'), ';
                                    }
                                    else 
                                    {
                                        $row_data .= '"'.$row[$i].'"), ';
                                    }
                                }
                                else 
                                {
                                    $row_data .= 'NULL), ';
                                }
                            }
                            else
                            {
                                // Last column of Last row in table data
                                if(isset($row[$i]))
                                {
                                   $val = (int)$row[$i];
                                    if($val)
                                    {
                                    $row_data .= $row[$i].'), ';
                                    }
                                    else 
                                    {
                                        $row_data .= '"'.$row[$i].'"), ';
                                    }
                                }
                                else 
                                {
                                    $row_data .= 'NULL)';
                                }
                            }
                        }
                    }

                    // Append row data to table data
                    $table_data .= $row_data;

                    $counter++;
                }
            }
            
            // Append table data to output stream
            if(strlen(@$table_data) > 10)
            {
                $output .= mb_substr($table_data, 0, -2)."; ";
            }
            
        }
        
        
        return $output;
        
    }
    
    
    public function getColumnDataType( $meta )
    {
        return $meta['native_type'];
    }
    
}

$t = new MobiSyncRemote();
//echo $t->getRemoteData();
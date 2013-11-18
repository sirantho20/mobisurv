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
class MobiSync 
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
    
    public function getSurveyTables()
    {
        $tables = array();
        $db = $this->local_db_instance;
        $prep = $db->prepare( 'select sid from surveys where active = "Y"' );
        $prep->execute();
        while ( $records = $prep->fetch(PDO::FETCH_BOTH, PDO::FETCH_ORI_NEXT) )
        {
            $tables[] = 'survey_'.$records[0];
        }
        
        return $tables;
    }
    /**
     * Gets the list of all survey ids belonging to active surveys
     * @return array A collection of survey id of all active surveys
     */
    public function getActiveSurveys()
    {
        $output = array();
        $db = $this->local_db_instance;
        $qr = $db->prepare( "select sid from surveys where active = 'Y'");
        $qr->execute();
        while ( $records = $qr->fetch(PDO::FETCH_BOTH, PDO::FETCH_ORI_NEXT) )
        {
            $cur = $records[0];
            $qr2 = $db->prepare( 'select surveyls_title from surveys_languagesettings where surveyls_survey_id = '.$cur);
            $qr2->execute();
            $res = $qr2->fetchAll();
            $out = $res[0][0];
            $output[] = array($records[0], $out);
        }
        
        return $output;
    }
    
    /**
     * Get summary statistics of survey
     * @param int Survey ID of the survey to get stats for
     * @return array Number total, incomplete and complete responses to the survey 
     */
    public function getSurveyStats($sid)
    {
        $db = $this->local_db_instance;
        $table = 'survey_'.$sid;
        $qr1 = $db->prepare( "select * from ".$table);
        $qr1->execute();
        $total = $qr1->rowCount();
        
        $qr2 = $db->prepare( 'select * from '.$table.' where submitdate is null');
        $qr2->execute();
        $incomplete = $qr2->rowCount();
        
        $complete = $total - $incomplete;
        
        return array('total'=>$total, 'incomplete'=>$incomplete, 'complete'=>$complete);
        
    }
    
    /**
     * Get browser accessible url for survey. This function uses the hostname and port number of server hosting limesurvey
     * @param int Survey Id of the survey to build url for
     * @return string complete url of the survey
     */
    public function getSurveyUrl($sid)
    {
        $hostname = $_SERVER['SERVER_ADDR'];
        $port = $_SERVER['SERVER_PORT'];
        
        return 'http://'.$hostname.':'.$port.'/index.php/'.$sid.'/lang-en';
    }

    /**
     * Gets all local survey answer records to uploaded to remote serer
     * 
     * @return string Returns a string of SQL insert statements for transmission to remote server
     */
    public function getLocalData($sid)
    {
        $output = '';
        $db = $this->local_db_instance;
        // Extract table data
        $survey_tables = array('survey_'.$sid);
        
        foreach ( $survey_tables as $table )
        {
            
            $query = 'select * from '.$table;
            $qr = $db->prepare($query);
            $qr->execute();
            $col_count = $qr->columnCount();
            $row_count = $qr->rowCount();
            
            
            if ( $row_count > 0 )
            {
                //$create_stmt = $this->getTableDefinitionSQL($table)."; ";
                
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
                                           $row_data .= '"'.$row[$i].'", ';
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
                                        $row_data .= '"'.$row[$i].'"), ';
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
                                    $row_data .= '"'.$row[$i].'"), ';
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
            $output[] = mb_substr($table_data, 0, -2);
        }
        
        return $output;
        
    }
    

    /**
     * Transfer exported local data to remote server as raw txt for processing
     * @param string $action Name of controller action to send request to
     * @return string Returns 'success' on success and error details on error
     */
    public function moveData($sid)
    {
        $data = array('action'=>'data_upload', 'value'=>$this->getLocalData($sid));
        $url = $this->core_object->api_base_url;
        $re = $this->core_object->transmit( $url, $data );
        
        if ( $re == 'success' )
        {
            $this->clearLocalData($sid);
            
            return true;
        }
        else 
        {
            $this->err = $re;
            return false;
        }
    }
    /**
     * Clear tables after uploading data to server
     * @param int Survey ID of the table to celar
     */
    public function clearLocalData( $sid ) 
    {
        $db = $this->local_db_instance;
        
        if( $sid == '' )
        {
            $tables = $this->getSurveyTables();
        }
        else 
        {
            $tables = array( 'survey_'.$sid );
        }
            
        foreach( $tables as $table )
        {
            
            $qr = $db->prepare( "TRUNCATE TABLE ".$table );
            $qr->execute();
            
        }
        
    }
    
    /**
     * Get all data data from remote server
     * @return string SQL insert statements to be executed on local database server
     */
    
    public function getRemoteUpdate()
    {
        $db = $this->local_db_instance;
        $remote_data = $this->core_object->transmit($this->core_object->api_base_url, array('action'=>'get_update'));
        
        if($remote_data)
        {
            
            try 
            {
                $prep = $db->query( $remote_data );
                $prep->execute();
                
                echo $remote_data;
                return true;
            }
            catch (Exception $ex) 
            {
                $this->err = $ex->getMessage();
                return false;
            }
        }
        else 
        {
            return $this->core_object->err;
        }
    }
    
    /**
     * Get table definition SQL
     * @param string Name of table to get DDL for
     * @return string Get SQL for table creation
     */
    
    public function getTableDefinitionSQL($table)
    {
        try 
        {
            $db = $this->local_db_instance;

            $qr = "SHOW CREATE TABLE ".$table;

            $query = $db->prepare($qr);
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);

            $drop = "DROP TABLE IF EXISTS ".$table."; ";

            return $drop.$result[0]['Create Table'];
        
        }
        catch (Exception $ex) 
        {
            echo $ex->getMessage();
            return false;
        }
        
    }
}



<?php

/**
 * Main class for MobiSurv terminal. Manages terminal activies and access control
 * @author Softcube Limited <info@softcube.co>
 * @link http://softcube.co Softcube
 * @copyright (c) 2013, All Rights Reserved
 * @version 1.0
 */

class MobiCore {
    
    public $local_db_host; 
    public $local_db_name;
    public $local_db_user;
    public $local_db_password;
    public $remote_db_host;
    public $remote_db_name;
    public $remote_db_user;
    public $remote_db_password;
    public $remote_db_type;
    public $api_base_url;
    public $err;
    public $account_id;
    public $terminal_email;
    public $terminal_key;


    public function __construct()
    {
        // Load configuration settings
        $this->loadConfig();
        //echo $this->transmit( $this->api_base_url, array('name' => 'Anthony Afetsrom', 'email' => 'sirantho20@gmail.com'));
        //die($this->api_base_url);
    }
    
    private function loadConfig()
    {
        $config = include ('config/main.php');
        
        foreach ( $config as $key => $value )
        {
            if( !isset( $this->$key ) )
            {
                $this->$key = $value;
            }
        }
    }
    
    /**
     * Transmit data between local and remote instances.
     * @param type $url
     * @param type $data
     * @return $string Returns string of response on success and false on error. 
     */
    public function transmit( $url, $data )
    {
        //return $data['action'];die();
        $comm = curl_init(); 
        curl_setopt( $comm, CURLOPT_URL, $url ); 
        curl_setopt( $comm, CURLOPT_VERBOSE, 0 ); 
        curl_setopt( $comm, CURLOPT_HEADER, 0 ); 
        curl_setopt( $comm, CURLOPT_POST, TRUE ); 
        curl_setopt( $comm, CURLOPT_SSL_VERIFYPEER, 0 ); 
        curl_setopt( $comm, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $comm, CURLOPT_POSTFIELDS, http_build_query( $data ) );
        //var_dump(curl_getinfo($comm)); die();
        $result = curl_exec( $comm );
        
        if ( curl_errno( $comm ) )
        {
            $this->err = curl_error( $comm );
            
            return false;
        }
        else
        {
            curl_close( $comm ); 
            
            return $result; 
        }

        
    }
    
    /**
     * Checks account status and performs necessary action
     */
    public function validateAccount()
    {
        $url = $this->api_base_url.'/validate_account';
        $data = array( 'account_id'=>$this->account_id, 'terminal_key'=>$this->terminal_key );
        $status = $this->transmit( $url, $data );
        
        if ( $status =='valid' )
        {
            return true;
        }
        elseif ( $status == 'decommissioned' ) 
        {
            // Decomission terminal
        }
    }
}

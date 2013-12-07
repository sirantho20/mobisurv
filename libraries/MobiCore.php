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
    public $account_name;
    public $update_archive_url;




    public function __construct()
    {
        //echo getcwd(); die();
        // Load configuration settings
        $this->loadConfig();
        //echo $this->transmit( $this->api_base_url, array('name' => 'Anthony Afetsrom', 'email' => 'sirantho20@gmail.com'));
        //die($this->api_base_url);
        
        $SID = session_id(); 
        if(empty($SID))
        {
            session_start();
        }

        $_SESSION['account_name'] = MobiCore::getAccountName();
        $_SESSION['db_name'] = $this->local_db_name;
        $_SESSION['db_user'] = $this->local_db_user;
        $_SESSION['db_password'] = $this->local_db_password;
        $_SESSION['db_host'] = $this->local_db_host;
    }
    
    public function sourceUpdate()
    {
        $download_url = $this->transmit($this->api_base_url, array('action'=>'request_source_update'));
        
        if($download_url != 'no')
        {
        $path = 'tmp/update.tar';
        
        $fp = fopen($path, 'w');
 
        $ch = curl_init($download_url);
        curl_setopt($ch, CURLOPT_FILE, $fp);

        curl_exec($ch);
        
        if( curl_errno( $ch ) )
        {
            $this->err = curl_error( $ch );
            return false;
        }
        else 
        {
            curl_close($ch);
            fclose($fp);
            
            $phar = new PharData('tmp/update.tar');
            $dir_old = getcwd();
            if($phar->extractTo('tmp',NULL,true))
            {
                $this->copyr('tmp/mobisurv', $dir_old);
                
                chdir('tmp');
                unlink('update.tar');
                $this->rrmdir('mobisurv');
                chdir($dir_old);
                return TRUE;
            }

            
            

        }
        }
    }
    /**
     * Resursively remote a directory and its content
     * @param string $dir Directory to delete 
     */
    public function rrmdir($dir) 
    {
        if (is_dir($dir)) {
          $objects = scandir($dir);
          foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
              if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else unlink($dir."/".$object);
            }
          }
          reset($objects);
          rmdir($dir);
        }
    }
    
    /**
     * Recursively copy content from source to destination
     * @param string $source Source to copy from
     * @param string $dest Destination to copy from
     */
    public function copyr($source, $dest)
    {
        
        if(is_dir($source)) {
            $dir_handle=opendir($source);
            while($file=readdir($dir_handle)){
                if($file!="." && $file!=".."){
                    if(is_dir($source."/".$file)){
                        @mkdir($dest."/".$file);
                        $this->copyr($source."/".$file, $dest."/".$file);
                    } else {
                        copy($source."/".$file, $dest."/".$file);
                    }
                }
            }
            closedir($dir_handle);
        } else {
            copy($source, $dest);
        }
    
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
    
    public static function getAccountName()
    {
        $conf = include 'config/main.php';
        return $conf['account_name'];
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
        $det = array('account_id' => $this->account_id, 'terminal_key' => $this->terminal_key);
        $data1 = array_merge($det, $data);
        
        $comm = curl_init(); 
        curl_setopt( $comm, CURLOPT_URL, $url ); 
        curl_setopt( $comm, CURLOPT_VERBOSE, 0 ); 
        curl_setopt( $comm, CURLOPT_HEADER, 0 ); 
        curl_setopt( $comm, CURLOPT_POST, TRUE ); 
        curl_setopt( $comm, CURLOPT_SSL_VERIFYPEER, 0 ); 
        curl_setopt( $comm, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $comm, CURLOPT_POSTFIELDS, http_build_query( $data1 ) );
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

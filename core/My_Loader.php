<?php defined('BASEPATH') OR exit('No direct script access allowed'); class MY_Loader extends CI_Loader { 
	/** * Database Loader * * @param mixed $params Database configuration options * @param bool $return Whether to return the database object * @param bool $query_builder Whether to enable Query Builder * (overrides the configuration setting) * * @return object|bool Database object if $return is set to TRUE, * FALSE on failure, CI_Loader instance in any other case */ 

public function database($params = '', $return = FALSE, $query_builder = NULL) { 
// Grab the super object 
	$CI = & get_instance(); 
// Do we even need to load the database class? 
	if ($return === FALSE && $query_builder === NULL && isset($CI->db) && is_object($CI->db) && !empty($CI->db->conn_id)) {
            return FALSE;
    }
    require_once(BASEPATH . 'database/DB.php');
        $DB = & DB($params, $query_builder);
        // ユーザードライバ読み込み
        $driver = config_item('subclass_prefix') . 'DB_' . $DB->dbdriver . '_driver';
        $driver_file = APPPATH . 'libraries/database/drivers/' . $DB->dbdriver . '/' . $driver . '.php';
        if (file_exists($driver_file)) {
            require_once($driver_file);
            $DB = new $driver(get_object_vars($DB));
            if (!empty($DB->subdriver)) {
                // ユーザーサブドライバ読み込み
                $driver = config_item('subclass_prefix') . 'DB_' . $DB->dbdriver . '_' . $DB->subdriver . '_driver';
                $driver_file = APPPATH . 'libraries/database/drivers/' . $DB->dbdriver . '/subdrivers/' . $driver . '.php';
                if (file_exists($driver_file)) {
                    require_once($driver_file);
                    $DB = new $driver(get_object_vars($DB));
                }
            }
            if($DB->initialize()){

            }else{
                echo 'Application database connection errors - '.date('Y-m-d H:i:s',strtotime('+8 hour',strtotime(date('Y-m-d H:i:s'))));
                exit;
            }
        }
        if ($return === TRUE) {
            return $DB;
        }
        // Initialize the db variable. Needed to prevent
        // reference errors with some configurations
        $CI->db = '';
        // Load the DB class
        $CI->db = $DB;
        return $this;
    }
}
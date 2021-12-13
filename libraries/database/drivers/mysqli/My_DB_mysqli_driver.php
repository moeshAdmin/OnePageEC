<?php defined('BASEPATH') OR exit('No direct script access allowed'); 

class MY_DB_mysqli_driver extends CI_DB_mysqli_driver { 
  final public function __construct($params) { 
    parent::__construct($params); 
  } 


  public function insert_on_duplicate_update_batch($table = '', $set = NULL, $escape = NULL) { 
        if ($set !== NULL) { 
          $this->set_insert_batch($set, '', $escape);
        }
        if (count($this->qb_set) === 0) {
            // No valid data array. Folds in cases where keys and values did not match up
            return ($this->db_debug) ? $this->display_error('db_must_use_set') : FALSE;
        }
        if ($table === '') {
            if (!isset($this->qb_from[0])) {
                return ($this->db_debug) ? $this->display_error('db_must_set_table') : FALSE;
            }
            $table = $this->qb_from[0];
        }
        // Batch this baby
        $affected_rows = 0;
        for ($i = 0, $total = count($this->qb_set); $i < $total; $i += 100) { $this->query($this->_insert_on_duplicate_key_update_batch($this->protect_identifiers($table, TRUE, $escape, FALSE), $this->qb_keys, array_slice($this->qb_set, $i, 100)));
            $affected_rows += $this->affected_rows();
        }
        $this->_reset_write();
        return $affected_rows;
  }
    /**
     * Insert on duplicate key update batch statement
     *
     * Generates a platform-specific insert string from the supplied data
     *
     * @param   string  $table  Table name
     * @param   array   $keys   INSERT keys
     * @param   array   $values INSERT values
     * @return  string
     */
    private function _insert_on_duplicate_key_update_batch($table, $keys, $values)
    {
        foreach ($keys as $num => $key) {
            $update_fields[] = $key . '= VALUES(' . $key . ')';
        }
        return "INSERT INTO " . $table . " (" . implode(', ', $keys) . ") VALUES " . implode(', ', $values) . " ON DUPLICATE KEY UPDATE " . implode(', ', $update_fields);
    }
}
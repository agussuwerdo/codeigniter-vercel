<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Log extends CI_Log {

    protected function _write_log($level, $msg) {
        $CI =& get_instance();
        $CI->load->database();

        // Ensure the log level and message are not empty
        if (empty($level) || empty($msg)) {
            return FALSE;
        }

        $data = array(
            'level' => $level,
            'message' => $msg,
        );

        // Insert log data into the database
        $CI->db->insert('logs', $data);

        return TRUE;
    }
}

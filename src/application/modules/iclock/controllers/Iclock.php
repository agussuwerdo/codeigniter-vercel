<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Iclock extends CI_Controller
{

  public function __construct()
  {
    parent::__construct();
    $this->load->helper('logger');
  }

  public function _remap($method, $params = array())
  {
    // Check if method exists
    if (method_exists($this, $method)) {
      // Call the method and capture its response
      ob_start();
      $response = call_user_func_array(array($this, $method), $params);
      $output = ob_get_clean();

      // If method returned a response, use that, otherwise use output buffer
      $final_response = $response ?: $output;

      // Log the response
      log_user_access($final_response);

      // Echo the response
      echo $final_response;
    } else {
      // Handle 404 or default to index
      $response = "Method not found: " . $method;
      log_user_access($response);
      show_404();
    }
  }

  public function index()
  {
    return "Hello World";
  }

  public function get_data()
  {
    return "OK";
  }

  /**
   * Initial information exchange between client and server
   * GET /iclock/cdata?SN={sn}&options=all&pushver={ver}&language={lang}
   */
  public function cdata()
  {
    $sn = $this->input->get('SN');
    $table = $this->input->get('table');

    if ($table) {
      return $this->edata();
    }

    // Build response according to protocol
    $response = "GET OPTION FROM: " . $sn . "\n";
    $response .= "ATTLOGStamp=None\n";
    $response .= "OPERLOGStamp=9999\n";
    $response .= "ATTPHOTOStamp=None\n";
    $response .= "ErrorDelay=30\n";
    $response .= "Delay=10\n";
    $response .= "TransTimes=00:00;14:05\n";
    $response .= "TransInterval=1\n";
    $response .= "TransFlag=TransData AttLog OpLog AttPhoto EnrollUser ChgUser EnrollFP ChgFP UserPic\n";
    $response .= "TimeZone=7\n"; // 7 for WIB timezone
    $response .= "Realtime=1\n";
    $response .= "ServerVer=2.2.14\n";
    $response .= "Encrypt=None";

    $this->output->set_content_type('text/plain');
    return $response;
  }

  /**
   * Handle data uploads from device
   * POST /iclock/edata?SN={sn}&table={table}&Stamp={stamp}
   */
  public function edata()
  {
    $sn = $this->input->get('SN');
    $table = $this->input->get('table');
    $stamp = $this->input->get('Stamp');
    $raw_data = $this->input->raw_input_stream;

    $record_count = 0;
    switch ($table) {
      case 'OPERLOG':
        $record_count = $this->_handle_operation_log($raw_data);
        break;
      case 'ATTPHOTO':
        $record_count = $this->_handle_attendance_photo($raw_data);
        break;
      case 'FACE':
        $record_count = $this->_handle_face_template($raw_data);
        break;
      case 'FP':
        $record_count = $this->_handle_fingerprint_template($raw_data);
        break;
      case 'ATTLOG':
        $record_count = $this->_handle_attendance_data($raw_data);
        break;
    }

    $this->output->set_content_type('text/plain');
    return "OK: " . $record_count;
  }

  /**
   * Get device requests/commands
   * GET /iclock/getrequest?SN={sn}
   */
  public function getrequest()
  {
    $sn = $this->input->get('SN');
    $info = $this->input->get('INFO');

    if ($info) {
      // Handle device info updates
      log_user_access("Device Info: " . $info);
      return "OK";
    }

    // Check for pending commands
    $command = $this->_get_pending_command($sn);
    if ($command) {
      return $command;
    }

    return "OK";
  }

  /**
   * Handle device command responses
   * POST /iclock/devicecmd?SN={sn}
   */
  public function devicecmd()
  {
    $sn = $this->input->get('SN');
    $raw_data = $this->input->raw_input_stream;

    log_user_access("Command Response: " . $raw_data);
    return "OK";
  }

  /**
   * Handle attendance data uploads
   */
  private function _handle_attendance_data($raw_data)
  {
    $sn = $this->input->get('SN');
    $records = explode("\n", trim($raw_data));

    foreach ($records as $record) {
      $fields = explode("\t", trim($record));
      if (count($fields) >= 7) {
        log_user_access("Attendance Data: " . json_encode([
          'serial_number' => $sn,
          'employee_pin' => $fields[0],
          'datetime' => $fields[1] . ' ' . $fields[2],
          'status' => $fields[3],
          'verify_mode' => $fields[4],
          'workcode' => $fields[5]
        ]));
      }
    }

    return count($records);
  }

  /**
   * Handle operation log data
   */
  private function _handle_operation_log($raw_data)
  {
    $sn = $this->input->get('SN');
    $records = explode("\n", trim($raw_data));

    foreach ($records as $record) {
      $fields = explode("\t", trim($record));
      if (count($fields) >= 7) {
        log_user_access("Operation Log: " . json_encode([
          'serial_number' => $sn,
          'operation' => $fields[2],
          'employee_pin' => $fields[1],
          'datetime' => $fields[0],
          'param1' => $fields[4],
          'param2' => $fields[5],
          'param3' => $fields[6]
        ]));
      }
    }

    return count($records);
  }

  /**
   * Handle attendance photo data
   */
  private function _handle_attendance_photo($raw_data)
  {
    $sn = $this->input->get('SN');
    $records = explode("\n", trim($raw_data));

    foreach ($records as $record) {
      $fields = explode("\t", trim($record));
      if (count($fields) >= 4) {
        log_user_access("Photo Data: " . json_encode([
          'serial_number' => $sn,
          'pin' => $fields[0],
          'datetime' => $fields[1],
          'photo_size' => $fields[2]
        ]));
      }
    }

    return count($records);
  }

  /**
   * Handle face template data
   */
  private function _handle_face_template($raw_data)
  {
    $sn = $this->input->get('SN');
    $records = explode("\n", trim($raw_data));

    foreach ($records as $record) {
      $fields = explode("\t", trim($record));
      if (count($fields) >= 5) {
        log_user_access("Face Template: " . json_encode([
          'serial_number' => $sn,
          'pin' => $fields[0],
          'face_id' => $fields[1],
          'size' => $fields[2]
        ]));
      }
    }

    return count($records);
  }

  /**
   * Handle fingerprint template data
   */
  private function _handle_fingerprint_template($raw_data)
  {
    $sn = $this->input->get('SN');
    $records = explode("\n", trim($raw_data));

    foreach ($records as $record) {
      $fields = explode("\t", trim($record));
      if (count($fields) >= 5) {
        log_user_access("Fingerprint Template: " . json_encode([
          'serial_number' => $sn,
          'pin' => $fields[0],
          'finger_id' => $fields[1],
          'size' => $fields[2]
        ]));
      }
    }

    return count($records);
  }

  /**
   * Get pending commands for device
   */
  private function _get_pending_command($sn)
  {
    // Example command format:
    // C:123:DATA UPDATE USERINFO PIN=123    Name=John    Passwd=    Card=    Grp=1    TZ=0    Pri=0
    // C:124:CHECK
    // C:125:CLEAR LOG
    return null; // Return command if any, null otherwise
  }
}

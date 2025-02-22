<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Hook extends CI_Controller
{

  private $redis_url;
  private $auth_token;

  public function __construct()
  {
    parent::__construct();
    $this->load->helper('logger');
    // Get environment variables
    $this->redis_url = getenv('REDIS_URL') ?: "";
    $this->auth_token = getenv('REDIS_AUTH_TOKEN') ?: "";
  }

  public function index()
  {
    log_user_access("welcome page");
    $this->load->view('welcome_message');
  }

  function view_logs()
  { // Just load the view, data will be fetched via AJAX
    $this->load->view('logs_view');
  }

  function get_logs()
  {
    header('Content-Type: application/json');

    try {
      // Get page number and limit from query params
      $page = $this->input->get('page') ? (int)$this->input->get('page') : 1;
      $limit = $this->input->get('limit') ? (int)$this->input->get('limit') : 10;
      $offset = ($page - 1) * $limit;

      // Get all keys matching the pattern
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $this->redis_url . "/keys/request_log:*");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $this->auth_token
      ]);

      $result = curl_exec($ch);
      curl_close($ch);

      $keys = json_decode($result, true);
      $logs = [];
      $total_logs = 0;

      if ($keys && is_array($keys['result'])) {
        // Reverse keys to get newest first
        $all_keys = array_reverse($keys['result']);
        $total_logs = count($all_keys);

        // Get only the keys for current page
        $page_keys = array_slice($all_keys, $offset, $limit);

        foreach ($page_keys as $key) {
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $this->redis_url . "/get/" . urlencode($key));
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->auth_token
          ]);

          $result = curl_exec($ch);
          curl_close($ch);

          $response = json_decode($result, true);

          if (isset($response['result'])) {
            $log_data = json_decode($response['result'], true);
            if ($log_data) {
              $logs[] = $log_data;
            }
          }
        }
      }

      echo json_encode([
        'success' => true,
        'logs' => $logs,
        'pagination' => [
          'total' => $total_logs,
          'per_page' => $limit,
          'current_page' => $page,
          'total_pages' => ceil($total_logs / $limit)
        ]
      ]);
    } catch (Exception $e) {
      http_response_code(500);
      echo json_encode(['success' => false, 'error' => 'Failed to fetch logs']);
    }
  }

  public function clear_logs()
  {
    header('Content-Type: application/json');

    try {
      // Get all keys matching the pattern
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $this->redis_url . "/keys/request_log:*");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $this->auth_token
      ]);

      $result = curl_exec($ch);
      curl_close($ch);

      $keys = json_decode($result, true);

      if ($keys && is_array($keys['result'])) {
        foreach ($keys['result'] as $key) {
          // Delete each key
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $this->redis_url . "/del/" . urlencode($key));
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->auth_token
          ]);

          curl_exec($ch);
          curl_close($ch);
        }
      }

      echo json_encode(['success' => true, 'message' => 'All logs cleared successfully']);
    } catch (Exception $e) {
      http_response_code(500);
      echo json_encode(['success' => false, 'error' => 'Failed to clear logs']);
    }
  }
}

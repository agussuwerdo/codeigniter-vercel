<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Hook extends CI_Controller
{

  private $redis_url = "https://tender-earwig-18229.upstash.io";
  private $auth_token = "AUc1AAIjcDE2MGM0NmRmZTI1MGY0ZTQwYjQ1ZmNmYTQzNGEzMWJhZHAxMA";

  public function index()
  {
    $this->log_user_access();
  }

  function log_user_access()
  {
    $CI = &get_instance();

    // Get request details
    $request_time = microtime(true);
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = $CI->uri->uri_string();
    $query = $_SERVER['QUERY_STRING'];
    $payload = file_get_contents('php://input');
    $ip = $CI->input->ip_address();

    // Get response details
    $status_code = http_response_code();
    $response_time = microtime(true) - $request_time;

    // Prepare data for Redis
    $log_data = json_encode([
      'timestamp' => date('Y-m-d H:i:s'),
      'method' => $method,
      'uri' => $uri,
      'query' => $query,
      'payload' => $payload,
      'status_code' => $status_code,
      'ip' => $ip,
      'request_time' => date('Y-m-d H:i:s', (int)$request_time),
      'response_time' => round($response_time * 1000, 2) // in milliseconds
    ]);

    // Call Upstash Redis
    $redis_url = "https://tender-earwig-18229.upstash.io";
    $auth_token = "AUc1AAIjcDE2MGM0NmRmZTI1MGY0ZTQwYjQ1ZmNmYTQzNGEzMWJhZHAxMA";

    // Generate unique key for the log entry
    $key = "request_log:" . uniqid();

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $redis_url . "/set/" . $key . "/" . urlencode($log_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Authorization: Bearer " . $auth_token
    ]);

    $result = curl_exec($ch);
    curl_close($ch);
    echo $result;
  }

  function view_logs()
  {
    // Get all keys matching the pattern
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $this->redis_url . "/keys/request_log:*");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $this->auth_token
    ]);

    $result = curl_exec($ch);
    curl_close($ch);
    
    // Decode the keys response
    $keys = json_decode($result, true);
    $logs = [];

    // Check if we have any keys and it's an array
    if ($keys && is_array($keys['result'])) {
        // Get values for each key
        foreach ($keys['result'] as $key) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->redis_url . "/get/" . urlencode($key));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer " . $this->auth_token
            ]);

            $result = curl_exec($ch);
            curl_close($ch);

            // Decode the value response
            $response = json_decode($result, true);
            
            if (isset($response['result'])) {
                // The actual log data is in the result
                $log_data = json_decode($response['result'], true);
                if ($log_data) {
                    $logs[] = $log_data;
                }
            }
        }

        // Reverse the array to show newest logs first
        $logs = array_reverse($logs);
    }

    // Load view with logs data
    $data['logs'] = $logs;
    $this->load->view('logs_view', $data);
  }
}

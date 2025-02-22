<?php
defined('BASEPATH') or exit('No direct script access allowed');

function log_user_access($response = null) {
    $enabled = getenv('LOG_USER_ACCESS') ?: false;
    if (!$enabled) {
        return;
    }

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

    // Base64 encode both payload and response
    $encoded_payload = $payload !== null ? base64_encode($payload) : null;
    $encoded_response = $response !== null ? base64_encode($response) : null;

    // Prepare data for Redis
    $log_data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'method' => $method,
        'uri' => $uri,
        'query' => $query,
        'payload' => $encoded_payload,
        'status_code' => $status_code,
        'ip' => $ip,
        'request_time' => date('Y-m-d H:i:s', (int)$request_time),
        'response_time' => round($response_time * 1000, 2), // in milliseconds
        'response' => $encoded_response
    ];

    // Get Redis credentials from environment
    $redis_url = getenv('REDIS_URL') ?: "";
    $auth_token = getenv('REDIS_AUTH_TOKEN') ?: "";

    // Generate unique key for the log entry
    $key = "request_log:" . uniqid();

    // JSON encode the data and URL encode for Redis storage
    $encoded_data = urlencode(json_encode($log_data));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $redis_url . "/set/" . $key . "/" . $encoded_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $auth_token
    ]);

    curl_exec($ch);
    curl_close($ch);
}

<?php
// Debug script to check what IP the API is detecting
// This script should be run on the HestiaCP server

echo "=== HestiaCP API IP Debug Script ===\n";
echo "Run this script on your HestiaCP server via web browser or as a web request\n\n";

// Set up a simulated web environment if running from CLI
if (php_sapi_name() === 'cli') {
    echo "WARNING: Running from CLI - simulating web environment\n";
    // Simulate basic web server variables
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['HTTP_HOST'] = 'localhost';
    $_SERVER['SERVER_NAME'] = 'localhost';
    $_SERVER['SERVER_PORT'] = '8083';
    
    // Try to get real IP from command line tools
    $external_ipv4 = trim(shell_exec('curl -4 -s ifconfig.me 2>/dev/null || echo "unavailable"'));
    $external_ipv6 = trim(shell_exec('curl -6 -s ifconfig.me 2>/dev/null || echo "unavailable"'));
    
    echo "External IPv4: $external_ipv4\n";
    echo "External IPv6: $external_ipv6\n\n";
    
    // Simulate the external IP as REMOTE_ADDR
    if ($external_ipv4 !== 'unavailable') {
        $_SERVER['REMOTE_ADDR'] = $external_ipv4;
    } elseif ($external_ipv6 !== 'unavailable') {
        $_SERVER['REMOTE_ADDR'] = $external_ipv6;
    } else {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }
}

// Check if HestiaCP helpers exist
$helpers_path = '/usr/local/hestia/web/inc/helpers.php';
if (!file_exists($helpers_path)) {
    echo "ERROR: HestiaCP helpers not found at $helpers_path\n";
    echo "Please run this script on your HestiaCP server\n";
    exit(1);
}

// Include the helpers to get the same IP detection logic
require_once $helpers_path;

echo "=== Server Environment Variables ===\n";
echo "REMOTE_ADDR: " . ($_SERVER['REMOTE_ADDR'] ?? 'not set') . "\n";
echo "HTTP_CLIENT_IP: " . ($_SERVER['HTTP_CLIENT_IP'] ?? 'not set') . "\n";
echo "HTTP_X_FORWARDED_FOR: " . ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'not set') . "\n";
echo "HTTP_FORWARDED_FOR: " . ($_SERVER['HTTP_FORWARDED_FOR'] ?? 'not set') . "\n";
echo "HTTP_X_FORWARDED: " . ($_SERVER['HTTP_X_FORWARDED'] ?? 'not set') . "\n";
echo "HTTP_FORWARDED: " . ($_SERVER['HTTP_FORWARDED'] ?? 'not set') . "\n";
echo "HTTP_CF_CONNECTING_IP: " . ($_SERVER['HTTP_CF_CONNECTING_IP'] ?? 'not set') . "\n";

echo "\n=== Detected IP ===\n";
$detected_ip = get_real_user_ip();
echo "get_real_user_ip(): '$detected_ip'\n";

echo "\n=== Current Configuration ===\n";
$hestia_cmd = '/usr/local/hestia/bin/v-list-sys-config';
if (!file_exists($hestia_cmd)) {
    echo "ERROR: HestiaCP command not found at $hestia_cmd\n";
    exit(1);
}

exec("sudo $hestia_cmd json", $output, $return_var);
if ($return_var !== 0) {
    echo "ERROR: Failed to execute HestiaCP command (exit code: $return_var)\n";
    echo "Output: " . implode("\n", $output) . "\n";
    exit(1);
}

$config = json_decode(implode("", $output), true);
if (!$config) {
    echo "ERROR: Failed to parse HestiaCP configuration JSON\n";
    exit(1);
}

echo "API_ALLOWED_IP: " . ($config['config']['API_ALLOWED_IP'] ?? 'not set') . "\n";
echo "API: " . ($config['config']['API'] ?? 'not set') . "\n";
echo "API_SYSTEM: " . ($config['config']['API_SYSTEM'] ?? 'not set') . "\n";

echo "\n=== IP Validation Test ===\n";
$allowed_ip_setting = $config['config']['API_ALLOWED_IP'] ?? '';

if ($allowed_ip_setting === 'allow-all') {
    echo "API allows all IPs\n";
} else {
    $allowed_ips = array_filter(explode(",", $allowed_ip_setting));
    $allowed_ips[] = ""; // Empty string is also checked in the original code
    
    echo "Detected IP: '$detected_ip'\n";
    echo "Allowed IPs: " . implode(", ", array_map(function($ip) { return "'$ip'"; }, $allowed_ips)) . "\n";
    echo "IP is allowed: " . (in_array($detected_ip, $allowed_ips) ? "YES" : "NO") . "\n";
    
    // Test if there are any hidden characters
    if ($detected_ip) {
        echo "\nDetected IP hex dump: " . bin2hex($detected_ip) . "\n";
        echo "Detected IP length: " . strlen($detected_ip) . "\n";
        
        // Check if it's IPv4 or IPv6
        if (filter_var($detected_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            echo "IP type: IPv4\n";
        } elseif (filter_var($detected_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            echo "IP type: IPv6\n";
        } else {
            echo "IP type: Invalid\n";
        }
    }
}

echo "\n=== Troubleshooting Suggestions ===\n";
if ($config['config']['API'] !== 'yes') {
    echo "⚠️  Legacy API is disabled (API='" . ($config['config']['API'] ?? 'not set') . "')\n";
    echo "   Fix: sudo /usr/local/hestia/bin/v-change-sys-config-value API yes\n";
}

if (($config['config']['API_SYSTEM'] ?? '0') === '0') {
    echo "⚠️  API System is disabled\n";
    echo "   Fix: Enable via web interface or command line\n";
}

if ($detected_ip && $allowed_ip_setting !== 'allow-all' && !in_array($detected_ip, explode(',', $allowed_ip_setting))) {
    echo "⚠️  Your IP '$detected_ip' is not in the allowed list\n";
    echo "   Fix: sudo /usr/local/hestia/bin/v-change-sys-config-value API_ALLOWED_IP \"$allowed_ip_setting,$detected_ip\"\n";
}

echo "\n=== Next Steps ===\n";
echo "1. Copy this script to your HestiaCP server\n";
echo "2. Run it via web browser: https://your-server:8083/debug_ip.php\n";
echo "3. Or run via CLI on the server: php debug_ip.php\n";
echo "4. Apply the suggested fixes above\n";

?>
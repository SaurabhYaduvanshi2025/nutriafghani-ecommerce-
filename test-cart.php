<?php
// Simple test script for cart functionality
session_start();
require_once('config/db.php');
require_once('includes/customer-auth.php');

// Simulate a logged-in customer for testing
$_SESSION['customer_id'] = 1; // Assuming customer ID 1 exists

// Test cart-process.php
$url = 'http://localhost/nutriafghan/cart-process.php';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . session_id()); // Pass session cookie

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";

$data = json_decode($response, true);
if ($data) {
    echo "\nDecoded JSON:\n";
    print_r($data);
} else {
    echo "\nFailed to decode JSON\n";
}
?>
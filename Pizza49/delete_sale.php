<?php
// Enhanced delete_sale.php script

// Set headers for JSON response and error reporting
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Include database configuration
include('db/config.php');

// Enable detailed error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Logging function
function logMessage($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message);
}

// Response array
$response = [
    'success' => false, 
    'message' => 'Unknown error occurred'
];

try {
    // Handle preflight OPTIONS request for CORS
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        throw new Exception('Invalid request method');
    }

    // Retrieve invoice number from GET parameters
    $order_code = $_GET['order_code'] ?? null;
    logMessage("Received invoice number: " . ($order_code ?? 'NULL'));

    // Strict validation of invoice number
    if ($order_code === null || $order_code === '' || $order_code === '0') {
        throw new Exception('Invalid invoice number provided');
    }

    // Convert to integer
    $order_code = intval($order_code);

    // Prepare delete statement with parameterized query
    $sql = "DELETE FROM shopsale3 WHERE order_code = ?";
    
    // Prepare and execute statement
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Statement preparation failed: ' . $conn->error);
    }

    $stmt->bind_param("i", $order_code);
    
    if (!$stmt->execute()) {
        throw new Exception('Delete execution failed: ' . $stmt->error);
    }

    // Check if any rows were affected
    $affected_rows = $stmt->affected_rows;
    $stmt->close();

    if ($affected_rows > 0) {
        $response = [
            'success' => true, 
            'message' => 'Sale deleted successfully',
            'affected_rows' => $affected_rows
        ];
        logMessage("Successfully deleted sale with invoice number: $order_code");
    } else {
        $response = [
            'success' => false, 
            'message' => 'No matching sale found',
            'order_code' => $order_code
        ];
        logMessage("No sale found with invoice number: $order_code");
    }

} catch (Exception $e) {
    // Catch and log any exceptions
    logMessage('Error: ' . $e->getMessage());
    $response = [
        'success' => false, 
        'message' => $e->getMessage()
    ];
    http_response_code(400);  // Bad request
}

// Send JSON response
echo json_encode($response);

// Close database connection
$conn->close();
?>
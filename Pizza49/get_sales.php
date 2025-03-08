<?php
header('Content-Type: application/json');
include('db/config.php');

// Get the sale type
$type = isset($_GET['type']) ? $conn->real_escape_string($_GET['type']) : '';

// Error logging
error_log("Received sale type: " . $type);

// Prepare the query based on the type
$query = '';
switch ($type) {
    case 'dastak':
        $query = "SELECT * FROM shopsale3 WHERE order_app = 'Dastak'";
        break;
    case 'foodpanda':
        $query = "SELECT * FROM shopsale3 WHERE order_app = 'Foodpanda'";
        break;
    case 'shop':
        $query = "SELECT * FROM shopsale3 WHERE order_app = 'Shop'";
        break;
    default:
        error_log("Invalid sale type received: " . $type);
        echo json_encode(['error' => 'Invalid sale type']);
        exit;
}

// Execute query
$result = $conn->query($query);

if ($result) {
    $sales = [];
    while ($row = $result->fetch_assoc()) {
        // Log each row to see the exact data
        error_log("Fetched sale row: " . print_r($row, true));
        $sales[] = $row;
    }

    // Check if any sales were found
    if (empty($sales)) {
        error_log("No sales found for type: " . $type);
    }

    echo json_encode($sales);
} else {
    // Log any database errors
    error_log("Database query error: " . $conn->error);
    echo json_encode(['error' => 'Database query failed']);
}

$conn->close();
?>
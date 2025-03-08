<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
include('db/config.php');

// Get the raw POST data
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (!$data) {
    die(json_encode(["success" => false, "message" => "Invalid JSON data"]));
}

// Process items to calculate individual product totals
$processedItems = [];
foreach ($data['items'] as $item) {
    $productTotal = floatval($item['price']) * floatval($item['quantity']);
    $processedItem = $item;
    $processedItem['productTotal'] = number_format($productTotal, 2, '.', '');
    $processedItems[] = $processedItem;
}

$productDetails = json_encode($processedItems);

// Fetch the percentage value for the specific app from the database
$appName = $conn->real_escape_string($data['type']);
$percentageQuery = "SELECT value FROM percentagevalue WHERE company_name = '$appName'";
$percentageResult = $conn->query($percentageQuery);
$percentageValue = $percentageResult && $percentageResult->num_rows > 0 
    ? $percentageResult->fetch_assoc()['value'] 
    : null;

$sql = "INSERT INTO shopsale3 (
    order_code, customer_name, customer_contact_number, product_details,
    order_app, app_percentage_value, total_amount, app_price,
    paid_amount, due_amount, grandtotal
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssssssdddd",
    $data['orderCode'], 
    $data['customerNumber'], 
    $data['contactNumber'], 
    $productDetails, 
    $appName, 
    $percentageValue, 
    $data['grandTotal'], 
    $data['appPrice'], 
    $data['paidAmount'], 
    $data['dueAmount'], 
    $data['grandTotal']
);

$result = $stmt->execute();

$response = $result
    ? ["success" => true, "message" => "Order inserted successfully", "invoice_number" => $conn->insert_id]
    : ["success" => false, "message" => "Error inserting order: " . $stmt->error];

$stmt->close();
$conn->close();

echo json_encode($response);
?>



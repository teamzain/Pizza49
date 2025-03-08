<?php
header('Content-Type: application/json');

// Database connection
include('db/config.php');


if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

$query = "SELECT company_name, value FROM percentagevalue";
$result = $conn->query($query);

$percentages = [];
while ($row = $result->fetch_assoc()) {
    $percentages[] = $row;
}

echo json_encode($percentages);

$conn->close();
?>
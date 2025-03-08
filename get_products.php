<?php
include('db/config.php');

$response = [];

$sql = "SELECT id, product_name, price FROM products";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $response[] = [
            'id' => $row['id'],
            'name' => $row['product_name'], 
            'price' => $row['price']
        ];
    }
}

$conn->close();

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
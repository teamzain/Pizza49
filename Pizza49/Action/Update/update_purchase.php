<?php
// Include the configuration file
include '../../db/config.php';

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $purchase_id = $_POST['purchase_id'];
    $paid_amount = $_POST['paid_amount'];
    $due_amount = $_POST['due_amount'];

    // Update the purchase record
    $updateQuery = "UPDATE purchase SET paid_amount = ?, due_amount = ? WHERE purchase_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ddi", $paid_amount, $due_amount, $purchase_id);

    if ($stmt->execute()) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $conn->error;
    }

    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>

<?php
// Include the database configuration file
include '../../db/config.php'; // Ensure the correct path to config.php

// Check if an ID is provided in the URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);  // Sanitize the ID

    // Prepare the SQL query to delete the customer
    $sql = "DELETE FROM customers WHERE customer_id = $id";

    // Execute the query
    if ($conn->query($sql) === TRUE) {
        // Redirect back to the customer registration page after deletion
        header('Location: ../../customer_registration.php'); 
    } else {
        // Display an error message if the query fails
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close the database connection
$conn->close();
?>

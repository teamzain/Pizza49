<?php

include '../../db/config.php'; // Ensure the correct path to config.php

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);  // Sanitize the ID

    // Prepare the SQL query to delete the customer
    $sql = "DELETE FROM vehicle WHERE id = $id";

    // Execute the query
    if ($conn->query($sql) === TRUE) {
        // Redirect back to the supplier registration page after deletion
        header('Location: ../../vehicle_registration.php'); 
    } else {
        // Display an error message if the query fails
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close the database connection
$conn->close();
?>
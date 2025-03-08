<?php
session_start();
@include'db/config.php';

function getLowStockNotifications($conn) {
    $notifications = [];
    $query = "SELECT name, total_quantity FROM stock WHERE total_quantity < 3";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $notifications[] = "Low stock alert: {$row['name']} (Quantity: {$row['total_quantity']})";
        }
    }

    return $notifications;
}
?>
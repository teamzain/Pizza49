<?php
 include '../../db/config.php';

 if (isset($_POST['barcode'])) {
    $barcode = $_POST['barcode'];
    
    // Query to fetch product name, stock, and price based on barcode_number
    $sql = "SELECT p.product_name, p.price, s.total_quantity, s.sold_quantity 
            FROM products p
            JOIN stock s ON p.id = s.product_id
            WHERE p.barcode_number = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $barcode);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Calculate available stock
        $available_stock = $row['total_quantity'];
        
        // Return product name, available stock, and price as JSON response
        echo json_encode([
            'success' => true,
            'product_name' => $row['product_name'],
            'available_stock' => $available_stock,
            'unit_price' => $row['price']
        ]);
    } else {
        echo json_encode(['success' => false]);
    }
    $stmt->close();
    $conn->close();
}

?>
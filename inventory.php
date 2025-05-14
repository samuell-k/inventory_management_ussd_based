<?php

function addProduct($user, $productName) {
    global $conn;
    
    try {
       
        $conn->begin_transaction();
        
        
        $stmt = $conn->prepare("INSERT INTO products (user_id, product_name) VALUES (?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparing product statement: " . $conn->error);
        }
        
        $stmt->bind_param("is", $user['id'], $productName);
        if (!$stmt->execute()) {
            throw new Exception("Error inserting product: " . $stmt->error);
        }
        
        $productId = $conn->insert_id;
        
       
        $stmt = $conn->prepare("INSERT INTO stock (product_id, quantity) VALUES (?, 0)");
        if (!$stmt) {
            throw new Exception("Error preparing stock statement: " . $conn->error);
        }
        
        $stmt->bind_param("i", $productId);
        if (!$stmt->execute()) {
            throw new Exception("Error initializing stock: " . $stmt->error);
        }
        
      
        $conn->commit();
        
        
        $user['inventory'][$productId] = [
            'name' => $productName,
            'quantity' => 0
        ];
        
        return true;
        
    } catch (Exception $e) {
       
        $conn->rollback();
        error_log("Error adding product: " . $e->getMessage());
        return false;
    }
}


function stockIn($user, $productName, $quantity) {
    global $conn;
    
    try {
       
        $conn->begin_transaction();
        
       
        $stmt = $conn->prepare("SELECT id FROM products WHERE user_id = ? AND product_name = ?");
        if (!$stmt) {
            throw new Exception("Error preparing product query: " . $conn->error);
        }
        
        $stmt->bind_param("is", $user['id'], $productName);
        if (!$stmt->execute()) {
            throw new Exception("Error finding product: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        
        if (!$product) {
            throw new Exception("Product not found");
        }
        
        $productId = $product['id'];
        
        
        $stmt = $conn->prepare("UPDATE stock SET quantity = quantity + ? WHERE product_id = ?");
        if (!$stmt) {
            throw new Exception("Error preparing stock update: " . $conn->error);
        }
        
        $stmt->bind_param("ii", $quantity, $productId);
        if (!$stmt->execute()) {
            throw new Exception("Error updating stock: " . $stmt->error);
        }
        
       
        $stmt = $conn->prepare("INSERT INTO stock_in (product_id, quantity) VALUES (?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparing stock in record: " . $conn->error);
        }
        
        $stmt->bind_param("ii", $productId, $quantity);
        if (!$stmt->execute()) {
            throw new Exception("Error recording stock in: " . $stmt->error);
        }
        
        
        $conn->commit();
        
       
        $user['inventory'][$productId]['quantity'] += $quantity;
        
        return true;
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error in stock in: " . $e->getMessage());
        return false;
    }
}


function stockOut($user, $productName, $quantity) {
    global $conn;
    
    try {
     
        $conn->begin_transaction();
        
        
        $stmt = $conn->prepare("SELECT p.id, s.quantity FROM products p 
                               JOIN stock s ON p.id = s.product_id 
                               WHERE p.user_id = ? AND p.product_name = ?");
        if (!$stmt) {
            throw new Exception("Error preparing product query: " . $conn->error);
        }
        
        $stmt->bind_param("is", $user['id'], $productName);
        if (!$stmt->execute()) {
            throw new Exception("Error finding product: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        
        if (!$product) {
            throw new Exception("Product not found");
        }
        
        if ($product['quantity'] < $quantity) {
            throw new Exception("Insufficient stock");
        }
        
        $productId = $product['id'];
        
      
        $stmt = $conn->prepare("UPDATE stock SET quantity = quantity - ? WHERE product_id = ?");
        if (!$stmt) {
            throw new Exception("Error preparing stock update: " . $conn->error);
        }
        
        $stmt->bind_param("ii", $quantity, $productId);
        if (!$stmt->execute()) {
            throw new Exception("Error updating stock: " . $stmt->error);
        }
        
     
        $stmt = $conn->prepare("INSERT INTO stock_out (product_id, quantity) VALUES (?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparing stock out record: " . $conn->error);
        }
        
        $stmt->bind_param("ii", $productId, $quantity);
        if (!$stmt->execute()) {
            throw new Exception("Error recording stock out: " . $stmt->error);
        }
        
        
        $conn->commit();
        
        
        $user['inventory'][$productId]['quantity'] -= $quantity;
        
        return true;
        
    } catch (Exception $e) {
        
        $conn->rollback();
        error_log("Error in stock out: " . $e->getMessage());
        return false;
    }
}


function viewStock($user) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT p.product_name, s.quantity 
                               FROM products p 
                               JOIN stock s ON p.id = s.product_id 
                               WHERE p.user_id = ?");
        if (!$stmt) {
            throw new Exception("Error preparing stock query: " . $conn->error);
        }
        
        $stmt->bind_param("i", $user['id']);
        if (!$stmt->execute()) {
            throw new Exception("Error fetching stock: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $output = '';
        $index = 1;
        
        while ($row = $result->fetch_assoc()) {
            $output .= $index . ". " . $row['product_name'] . " - " . $row['quantity'] . "\n";
            $index++;
        }
        
        return $output ?: "No products in inventory.";
        
    } catch (Exception $e) {
        error_log("Error viewing stock: " . $e->getMessage());
        return "Error retrieving stock information.";
    }
}


function stockInById($user, $productId, $quantity) {
    global $conn;
    
    try {
       
        $conn->begin_transaction();
        
      
        $stmt = $conn->prepare("UPDATE stock SET quantity = quantity + ? WHERE product_id = ?");
        if (!$stmt) {
            throw new Exception("Error preparing stock update: " . $conn->error);
        }
        
        $stmt->bind_param("ii", $quantity, $productId);
        if (!$stmt->execute()) {
            throw new Exception("Error updating stock: " . $stmt->error);
        }
        
        
        $stmt = $conn->prepare("INSERT INTO stock_in (product_id, quantity) VALUES (?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparing stock in record: " . $conn->error);
        }
        
        $stmt->bind_param("ii", $productId, $quantity);
        if (!$stmt->execute()) {
            throw new Exception("Error recording stock in: " . $stmt->error);
        }
        
      
        $conn->commit();
        return true;
        
    } catch (Exception $e) {
        
        $conn->rollback();
        error_log("Error in stock in: " . $e->getMessage());
        return false;
    }
}


function stockOutById($user, $productId, $quantity) {
    global $conn;
    
    try {
      
        $conn->begin_transaction();
        
       
        $stmt = $conn->prepare("SELECT quantity FROM stock WHERE product_id = ?");
        if (!$stmt) {
            throw new Exception("Error preparing stock query: " . $conn->error);
        }
        
        $stmt->bind_param("i", $productId);
        if (!$stmt->execute()) {
            throw new Exception("Error checking stock: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $stock = $result->fetch_assoc();
        
        if (!$stock || $stock['quantity'] < $quantity) {
            throw new Exception("Insufficient stock");
        }
        
        
        $stmt = $conn->prepare("UPDATE stock SET quantity = quantity - ? WHERE product_id = ?");
        if (!$stmt) {
            throw new Exception("Error preparing stock update: " . $conn->error);
        }
        
        $stmt->bind_param("ii", $quantity, $productId);
        if (!$stmt->execute()) {
            throw new Exception("Error updating stock: " . $stmt->error);
        }
        
       
        $stmt = $conn->prepare("INSERT INTO stock_out (product_id, quantity) VALUES (?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparing stock out record: " . $conn->error);
        }
        
        $stmt->bind_param("ii", $productId, $quantity);
        if (!$stmt->execute()) {
            throw new Exception("Error recording stock out: " . $stmt->error);
        }
        
       
        $conn->commit();
        return true;
        
    } catch (Exception $e) {
        
        $conn->rollback();
        error_log("Error in stock out: " . $e->getMessage());
        return false;
    }
} 
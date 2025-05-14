<?php


function createUser($phoneNumber, $username, $password) {
    global $conn;
    
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
   
    $stmt = $conn->prepare("INSERT INTO users (phone_number, username, password) VALUES (?, ?, ?)");
    if (!$stmt) {
        error_log("Error preparing statement: " . $conn->error);
        return false;
    }
    
    
    $stmt->bind_param("sss", $phoneNumber, $username, $hashedPassword);
    
  
    if (!$stmt->execute()) {
        error_log("Error creating user: " . $stmt->error);
        return false;
    }
    
    
    $userId = $conn->insert_id;
    
    
    $user = [
        'id' => $userId,
        'phone' => $phoneNumber,
        'username' => $username,
        'password' => $hashedPassword,
        'inventory' => []
    ];
    
    $stmt->close();
    return $user;
}


function getUserByPhone($phoneNumber) {
    global $conn;
    
    
    $stmt = $conn->prepare("SELECT id, phone_number, username, password FROM users WHERE phone_number = ?");
    if (!$stmt) {
        error_log("Error preparing statement: " . $conn->error);
        return null;
    }
    
   
    $stmt->bind_param("s", $phoneNumber);
    
    
    if (!$stmt->execute()) {
        error_log("Error getting user: " . $stmt->error);
        return null;
    }
    
   
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
    
    if (!$userData) {
        $stmt->close();
        return null;
    }
    
    
    $inventory = getUserInventory($userData['id']);
    
   
    $user = [
        'id' => $userData['id'],
        'phone' => $userData['phone_number'],
        'username' => $userData['username'],
        'password' => $userData['password'],
        'inventory' => $inventory
    ];
    
    $stmt->close();
    return $user;
}


function getUserInventory($userId) {
    global $conn;
    

    $stmt = $conn->prepare("SELECT id, product_name, quantity FROM inventory WHERE user_id = ?");
    if (!$stmt) {
        error_log("Error preparing statement: " . $conn->error);
        return [];
    }
    
   
    $stmt->bind_param("i", $userId);
    
    
    if (!$stmt->execute()) {
        error_log("Error getting inventory: " . $stmt->error);
        return [];
    }
    
  
    $result = $stmt->get_result();
    $inventory = [];
    
    while ($row = $result->fetch_assoc()) {
        $inventory[$row['id']] = [
            'name' => $row['product_name'],
            'quantity' => $row['quantity']
        ];
    }
    
    $stmt->close();
    return $inventory;
}


function verifyPassword($user, $password) {
    return password_verify($password, $user['password']);
}


function setUserSession($phoneNumber, $data) {
    $GLOBALS['sessions'][$phoneNumber] = array_merge(
        $GLOBALS['sessions'][$phoneNumber] ?? [],
        $data
    );
}


function getUserSession($phoneNumber) {
    return $GLOBALS['sessions'][$phoneNumber] ?? [];
}


function clearUserSession($phoneNumber) {
    unset($GLOBALS['sessions'][$phoneNumber]);
} 
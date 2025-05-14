<?php

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'inventory_ussd';


$conn = new mysqli($db_host, $db_user, $db_pass);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if ($conn->query($sql) === TRUE) {
   
    $conn->select_db($db_name);
    
  
    $tables_sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        phone_number VARCHAR(20) UNIQUE NOT NULL,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS inventory (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_name VARCHAR(100) NOT NULL,
        quantity INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    );

    CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        transaction_type ENUM('stock_in', 'stock_out') NOT NULL,
        quantity INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (product_id) REFERENCES inventory(id)
    );";

   
    $queries = explode(';', $tables_sql);
    foreach ($queries as $query) {
        if (trim($query) != '') {
            if (!$conn->query($query)) {
                die("Error creating table: " . $conn->error);
            }
        }
    }
} else {
    die("Error creating database: " . $conn->error);
}




define('SESSION_TIMEOUT', 300); 


define('ERROR_INVALID_PASSWORD', 'Invalid password. Please try again.');
define('ERROR_INSUFFICIENT_STOCK', 'Error: Insufficient stock!');
define('ERROR_INVALID_OPTION', 'Invalid option selected.');


define('SUCCESS_ACCOUNT_CREATED', 'Account created successfully! You will receive an SMS shortly.');
define('SUCCESS_PRODUCT_ADDED', 'Product added successfully!');
define('SUCCESS_STOCK_UPDATED', 'Stock updated successfully!');


$GLOBALS['sessions'] = []; 
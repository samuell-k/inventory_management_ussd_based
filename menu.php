<?php
require_once 'util.php';
require_once 'config.php';
require_once 'users.php';
require_once 'inventory.php';
require_once 'sms.php';

class Menu {
    private $conn;

    function __construct() {
        global $conn;
        if (!$conn) {
            die("Database connection not available");
        }
        $this->conn = $conn;
    }

    public function mainMenuUnregistered() {
        $response = "CON Welcome to Inventory Management System\n";
        $response .= "1. Register\n";
       
        header('Content-type: text/plain');
        echo $response;
    }

    public function menuRegister($textArray, $phoneNumber) {
        $level = count($textArray);
        $response = '';

        if ($level == 1) {
            $response = "CON Enter your username\n";
            $response .= "98. Back\n";
            
        } else if ($level == 2) {
            $response = "CON Enter your password\n";
            $response .= "98. Back\n";
            $response .= "99. Main Menu";
        } else if ($level == 3) {
            $response = "CON Confirm your password\n";
            $response .= "98. Back\n";
            $response .= "99. Main Menu";
        } else if ($level == 4) {
            $username = $textArray[1];
            $password = $textArray[2];
            $confirmPassword = $textArray[3];
            if ($password !== $confirmPassword) {
                $response = "END Error: Passwords do not match. Please try again.";
            } else {
                $response = "CON Confirm registration?\n1. Confirm\n2. Cancel\n";
                $response .= "98. Back\n";
                $response .= "99. Main Menu";
            }
        } else if ($level == 5) {
            $username = $textArray[1];
            $password = $textArray[2];
            $confirmPassword = $textArray[3];
            $action = $textArray[4];
            if ($action == '1') {
                $newUser = createUser($phoneNumber, $username, $password);
                $smsMessage = "Welcome $username! Your inventory account is ready. Start managing your stock via USSD.";
                $smsStatus = sendSMS($phoneNumber, $smsMessage);
                $response = "END Account created successfully! You will receive an SMS shortly.";
            } else {
                $response = "END Registration cancelled.";
            }
        }

        header('Content-type: text/plain');
        echo $response;
    }

    public function mainMenuRegistered() {
        $response = "CON Main Menu\n";
        $response .= "1. Add Product\n";
        $response .= "2. Stock In\n";
        $response .= "3. Stock Out\n";
        $response .= "4. View Stock\n";
        $response .= "5. View Products\n";
        $response .= "6. View Stock In History\n";
        $response .= "7. View Stock Out History";
        header('Content-type: text/plain');
        echo $response;
    }

    public function menuAddProduct($textArray, $phoneNumber) {
        $level = count($textArray);
        $user = getUserByPhone($phoneNumber);
        $response = '';

        if ($level == 1) {
            $response = "CON Enter product name:\n";
            $response .= "98. Back\n";
         
        } else if ($level == 2) {
            $productName = $textArray[1];
            $response = "CON Confirm adding product '$productName'?\n1. Confirm\n2. Cancel\n";
            $response .= "98. Back\n";
            $response .= "99. Main Menu";
        } else if ($level == 3) {
            $productName = $textArray[1];
            $action = $textArray[2];
            if ($action == '1') {
                addProduct($user, $productName);
                $smsMessage = "Product '$productName' has been added to your inventory successfully.";
                $smsStatus = sendSMS($phoneNumber, $smsMessage);
                $response = "END Product added successfully! You will receive an SMS shortly.";
            } else {
                $response = "END Add product cancelled.";
            }
        }

        header('Content-type: text/plain');
        echo $response;
    }

    public function menuStockIn($textArray, $phoneNumber) {
        $level = count($textArray);
        $user = getUserByPhone($phoneNumber);
        $response = '';

        if ($level == 1) {
            $response = "CON Enter product name:\n";
            $response .= "98. Back\n";
          
        } else if ($level == 2) {
            $productName = $textArray[1];
            $response = "CON Enter quantity to add:\n";
            $response .= "98. Back\n";
            $response .= "99. Main Menu";
        } else if ($level == 3) {
            $productName = $textArray[1];
            $quantity = (int)$textArray[2];
            if ($quantity <= 0) {
                $response = "END Error: Please enter a valid quantity greater than 0.";
            } else {
                $response = "CON Confirm adding $quantity to '$productName'?\n1. Confirm\n2. Cancel\n";
                $response .= "98. Back\n";
                $response .= "99. Main Menu";
            }
        } else if ($level == 4) {
            $productName = $textArray[1];
            $quantity = (int)$textArray[2];
            $action = $textArray[3];
            if ($action == '1') {
                global $conn;
                $stmt = $conn->prepare("SELECT id FROM products WHERE product_name = ?");
                $stmt->bind_param("s", $productName);
                $stmt->execute();
                $result = $stmt->get_result();
                $product = $result->fetch_assoc();
                if (!$product) {
                    $response = "END Error: Product not found.";
                } else {
                    if (stockInById($user, $product['id'], $quantity)) {
                        $smsMessage = "Stock In: Added $quantity units of '$productName' to inventory.";
                        $smsStatus = sendSMS($phoneNumber, $smsMessage);
                        $response = "END Stock updated successfully! You will receive an SMS shortly.";
                    } else {
                        $response = "END Error: Failed to update stock. Please try again.";
                    }
                }
            } else {
                $response = "END Stock in cancelled.";
            }
        }

        header('Content-type: text/plain');
        echo $response;
    }

    public function menuStockOut($textArray, $phoneNumber) {
        $level = count($textArray);
        $user = getUserByPhone($phoneNumber);
        $response = '';

        if ($level == 1) {
            $response = "CON Enter product name:\n";
            $response .= "98. Back\n";
            
        } else if ($level == 2) {
            $productName = $textArray[1];
            $response = "CON Enter quantity to remove:\n";
            $response .= "98. Back\n";
            $response .= "99. Main Menu";
        } else if ($level == 3) {
            $productName = $textArray[1];
            $quantity = (int)$textArray[2];
            if ($quantity <= 0) {
                $response = "END Please enter a valid quantity greater than 0.";
            } else {
                $response = "CON Confirm removing $quantity from '$productName'?\n1. Confirm\n2. Cancel\n";
                $response .= "98. Back\n";
                $response .= "99. Main Menu";
            }
        } else if ($level == 4) {
            $productName = $textArray[1];
            $quantity = (int)$textArray[2];
            $action = $textArray[3];
            if ($action == '1') {
                global $conn;
                $stmt = $conn->prepare("SELECT p.id, s.quantity FROM products p JOIN stock s ON p.id = s.product_id WHERE p.product_name = ?");
                $stmt->bind_param("s", $productName);
                $stmt->execute();
                $result = $stmt->get_result();
                $product = $result->fetch_assoc();
                if (!$product) {
                    $response = "END Product not found.";
                } else if ($product['quantity'] < $quantity) {
                    $response = "END Insufficient stock! Current stock: " . $product['quantity'];
                } else {
                    if (stockOutById($user, $product['id'], $quantity)) {
                        $smsMessage = "Stock Out: Removed $quantity units of '$productName' from inventory.";
                        $smsStatus = sendSMS($phoneNumber, $smsMessage);
                        $response = "END Stock updated successfully! You will receive an SMS shortly.";
                    } else {
                        $response = "END Error: Failed to update stock. Please try again.";
                    }
                }
            } else {
                $response = "END Stock out cancelled.";
            }
        }

        header('Content-type: text/plain');
        echo $response;
    }

    public function menuViewStock($textArray, $phoneNumber) {
        global $conn;
        
        $stmt = $conn->prepare("
            SELECT p.product_name, p.user_id, s.quantity 
            FROM products p 
            JOIN stock s ON p.id = s.product_id 
            ORDER BY p.product_name
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $response = "END Current Stock:\n";
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $response .= "Product: " . $row['product_name'] . "\n";
                $response .= "User ID: " . $row['user_id'] . "\n";
                $response .= "Quantity: " . $row['quantity'] . "\n";
                $response .= "-------------------\n";
            }
        } else {
            $response .= "No stock found.";
        }
        
        header('Content-type: text/plain');
        echo $response;
    }

    public function menuViewProducts($textArray, $phoneNumber) {
        global $conn;
        
        $stmt = $conn->prepare("SELECT id, product_name, description, user_id FROM products");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $response = "END All Products:\n";
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $response .= "Product: " . $row['product_name'] . "\n";
                $response .= "ID: " . $row['id'] . "\n";
                $response .= "User ID: " . $row['user_id'] . "\n";
                if ($row['description']) {
                    $response .= "Description: " . $row['description'] . "\n";
                }
                $response .= "-------------------\n";
            }
        } else {
            $response .= "No products found.";
        }
        
        header('Content-type: text/plain');
        echo $response;
    }

    public function menuViewStockInHistory($textArray, $phoneNumber) {
        global $conn;
        
        $stmt = $conn->prepare("
            SELECT p.product_name, p.user_id, si.quantity, si.transaction_date, si.notes 
            FROM stock_in si 
            JOIN products p ON si.product_id = p.id 
            ORDER BY si.transaction_date DESC 
            LIMIT 10
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $response = "END Recent Stock In History:\n";
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $response .= "Product: " . $row['product_name'] . "\n";
                $response .= "User ID: " . $row['user_id'] . "\n";
                $response .= "Quantity: " . $row['quantity'] . "\n";
                $response .= "Date: " . $row['transaction_date'] . "\n";
                if ($row['notes']) {
                    $response .= "Notes: " . $row['notes'] . "\n";
                }
                $response .= "-------------------\n";
            }
        } else {
            $response .= "No stock in history found.";
        }
        
        header('Content-type: text/plain');
        echo $response;
    }

    public function menuViewStockOutHistory($textArray, $phoneNumber) {
        global $conn;
        
        $stmt = $conn->prepare("
            SELECT p.product_name, p.user_id, so.quantity, so.transaction_date, so.notes 
            FROM stock_out so 
            JOIN products p ON so.product_id = p.id 
            ORDER BY so.transaction_date DESC 
            LIMIT 10
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $response = "END Recent Stock Out History:\n";
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $response .= "Product: " . $row['product_name'] . "\n";
                $response .= "User ID: " . $row['user_id'] . "\n";
                $response .= "Quantity: " . $row['quantity'] . "\n";
                $response .= "Date: " . $row['transaction_date'] . "\n";
                if ($row['notes']) {
                    $response .= "Notes: " . $row['notes'] . "\n";
                }
                $response .= "-------------------\n";
            }
        } else {
            $response .= "No stock out history found.";
        }
        
        header('Content-type: text/plain');
        echo $response;
    }

    public function goBack($text) {
        $xplodedText = explode("*", $text);
        while (array_search(Util::$GO_BACK, $xplodedText) != false) {
            $firstIndex = array_search(Util::$GO_BACK, $xplodedText);
            array_splice($xplodedText, $firstIndex - 1, 2);
        }
        return join("*", $xplodedText);
    }

    public function goToMainMenu($text) {
        $explodedText = explode("*", $text);
        while (array_search(Util::$GO_TO_MAIN_MENU, $explodedText) != false) {
            $firstindex = array_search(Util::$GO_TO_MAIN_MENU, $explodedText);
            $explodedText = array_slice($explodedText, $firstindex + 1);
        }
        return join("*", $explodedText);
    }

    public function middleware($text) {
        return $this->goBack($this->goToMainMenu($text));
    }
} 
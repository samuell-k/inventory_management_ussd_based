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
        $response .= "1. Register";
        header('Content-type: text/plain');
        echo $response;
    }

    public function menuRegister($textArray, $phoneNumber) {
        $level = count($textArray);
        $response = '';

        if ($level == 1) {
            $response = "CON Enter your username\n";
        } else if ($level == 2) {
            $response = "CON Enter your password\n";
        } else if ($level == 3) {
            $username = $textArray[1];
            $password = $textArray[2];
            
          
            $newUser = createUser($phoneNumber, $username, $password);
            
            
            $smsMessage = "Welcome $username! Your inventory account is ready. Start managing your stock via USSD.";
            $smsStatus = sendSMS($phoneNumber, $smsMessage);
            
            $response = "END Account created successfully! You will receive an SMS shortly.";
        }

        header('Content-type: text/plain');
        echo $response;
    }

    public function mainMenuRegistered() {
        $response = "CON Main Menu\n";
        $response .= "1. Add Product\n";
        $response .= "2. Stock In\n";
        $response .= "3. Stock Out\n";
        $response .= "4. View Stock";
       
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
            $response .= "99. Main Menu";
        } else {
            $productName = $textArray[1];
            addProduct($user, $productName);
            $response = "END Product added successfully!";
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
            $response .= "99. Main Menu";
        } else if ($level == 2) {
            $response = "CON Enter quantity to add:";
            $response .= "98. Back\n";
            $response .= "99. Main Menu";
        } else {
            $productName = $textArray[1];
            $quantity = (int)$textArray[2];
            
            if ($quantity <= 0) {
                $response = "END Error: Please enter a valid quantity greater than 0.\n";
                $response .= "98. Back\n";
                $response .= "99. Main Menu";
            } else {
               
                global $conn;
                $stmt = $conn->prepare("SELECT id FROM products WHERE user_id = ? AND product_name = ?");
                $stmt->bind_param("is", $user['id'], $productName);
                $stmt->execute();
                $result = $stmt->get_result();
                $product = $result->fetch_assoc();
                
                if (!$product) {
                    $response = "END Error: Product not found.";
                } else {
                   
                    if (stockInById($user, $product['id'], $quantity)) {
                        $response = "END Stock updated successfully!";
                    } else {
                        $response = "END Error: Failed to update stock. Please try again.";
                    }
                }
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
            $response .= "99. Main Menu";
        } else if ($level == 2) {
            $response = "CON Enter quantity to remove:\n";
            $response .= "98. Back\n";
            $response .= "99. Main Menu";
        } else {
            $productName = $textArray[1];
            $quantity = (int)$textArray[2];
            
            if ($quantity <= 0) {
                $response = "END Error: Please enter a valid quantity greater than 0.\n";
                $response .= "98. Back\n";
                $response .= "99. Main Menu";
            } else {
               
                global $conn;
                $stmt = $conn->prepare("SELECT p.id, s.quantity FROM products p 
                                      JOIN stock s ON p.id = s.product_id 
                                      WHERE p.user_id = ? AND p.product_name = ?");
                $stmt->bind_param("is", $user['id'], $productName);
                $stmt->execute();
                $result = $stmt->get_result();
                $product = $result->fetch_assoc();
                
                if (!$product) {
                    $response = "END Error: Product not found.";
                } else if ($product['quantity'] < $quantity) {
                    $response = "END Error: Insufficient stock! Current stock: " . $product['quantity'];
                } else {
                    
                    if (stockOutById($user, $product['id'], $quantity)) {
                        $response = "END Stock updated successfully!";
                    } else {
                        $response = "END Error: Failed to update stock. Please try again.";
                    }
                }
            }
        }

        header('Content-type: text/plain');
        echo $response;
    }

    public function menuViewStock($textArray, $phoneNumber) {
        $user = getUserByPhone($phoneNumber);
        $stock = viewStock($user);
        $response = "END Current Stock:\n" . $stock;
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
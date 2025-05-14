<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);




require_once 'config.php';
require_once 'users.php';
require_once 'inventory.php';
require_once 'sms.php';
require_once 'menu.php';


$sessionId = $_POST['sessionId'] ?? '';
$serviceCode = $_POST['serviceCode'] ?? '';
$phoneNumber = $_POST['phoneNumber'] ?? '';
$text = $_POST['text'] ?? '';


error_log("Received POST data: " . print_r($_POST, true));


$menu = new Menu();


$text = $menu->middleware($text);
$textArray = explode('*', $text);


$response = '';


if (!Util::isRegistered($phoneNumber)) {
    if (empty($text)) {
        $response = "CON Welcome to Inventory Management System\n";
        $response .= "1. Register\n";
    } else {
        $menu->menuRegister($textArray, $phoneNumber);
        return; 
    }
} else {
    if (empty($text)) {
        $response = "CON Main Menu\n";
        $response .= "1. Add Product\n";
        $response .= "2. Stock In\n";
        $response .= "3. Stock Out\n";
        $response .= "4. View Stock\n";
       
    } else {
        $menuOption = $textArray[0];
        
        switch ($menuOption) {
            case Util::$ADD_PRODUCT:
                $menu->menuAddProduct($textArray, $phoneNumber);
                return; 
                
            case Util::$STOCK_IN:
                $menu->menuStockIn($textArray, $phoneNumber);
                return; 
                
            case Util::$STOCK_OUT:
                $menu->menuStockOut($textArray, $phoneNumber);
                return; 
                
            case Util::$VIEW_STOCK:
                $menu->menuViewStock($textArray, $phoneNumber);
                return; 
                
            default:
                $response = "END Invalid option selected.";
        }
    }
}


header('Content-type: text/plain');
echo $response; 
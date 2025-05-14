<?php
class Util {
    
    public static $GO_BACK = "98";
    public static $GO_TO_MAIN_MENU = "99";
    
   
    public static $CON = "CON";
    public static $END = "END";
    
    
    public static $REGISTER = "1";
    public static $ADD_PRODUCT = "1";
    public static $STOCK_IN = "2";
    public static $STOCK_OUT = "3";
    public static $VIEW_STOCK = "4";
    
    
    public static function formatResponse($type, $message) {
        return $type . " " . $message;
    }
    
   
    public static function isRegistered($phoneNumber) {
        global $conn;
        
      
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE phone_number = ?");
        if (!$stmt) {
            error_log("Error preparing statement: " . $conn->error);
            return false;
        }
        
     
        $stmt->bind_param("s", $phoneNumber);
        
        
        if (!$stmt->execute()) {
            error_log("Error checking user registration: " . $stmt->error);
            return false;
        }
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $stmt->close();
        
        
        return $row['count'] > 0;
    }
} 
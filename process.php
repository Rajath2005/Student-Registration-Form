<?php
// Clean output buffer to prevent any HTML output
ob_start();

// Disable error display (errors will be returned in JSON)
error_reporting(0);
ini_set('display_errors', 0);

// Clear any output that might have been sent
ob_clean();

// Set JSON header
header('Content-Type: application/json');

// Database configuration - UPDATE WITH YOUR CREDENTIALS
$servername = "sql102.infinityfree.com";  // Change to your host (e.g., sql123.infinityfree.com)
$username = "if0_40272780";  // Your database username
$password = "RpO6nuMBS83c";  // Your database password
$dbname = "if0_40272780_students";// Change to YOUR database name

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if all required fields exist
        $required_fields = ['fullName', 'email', 'phone', 'dob', 'gender', 'course', 'address'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                echo json_encode(array('error' => 'Missing required field: ' . $field));
                exit;
            }
        }
        
        // Sanitize and validate input data
        $data = array(
            'fullName' => sanitizeInput($_POST['fullName']),
            'email' => sanitizeInput($_POST['email']),
            'phone' => sanitizeInput($_POST['phone']),
            'dob' => sanitizeInput($_POST['dob']),
            'gender' => sanitizeInput($_POST['gender']),
            'course' => sanitizeInput($_POST['course']),
            'address' => sanitizeInput($_POST['address'])
        );
        
        // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(array('error' => 'Invalid email address'));
            exit;
        }
        
        // Validate phone number
        if (!preg_match('/^[0-9]{10}$/', $data['phone'])) {
            echo json_encode(array('error' => 'Invalid phone number. Must be 10 digits.'));
            exit;
        }
        
        // Save to database
        $result = saveToDatabase($data, $servername, $username, $password, $dbname);
        
        if ($result['success']) {
            $data['registrationDate'] = date('Y-m-d H:i:s');
            $data['registrationId'] = $result['id'];
            
            // Clean output buffer before sending JSON
            ob_clean();
            echo json_encode($data);
        } else {
            ob_clean();
            echo json_encode(array('error' => 'Database error: ' . $result['message']));
        }
    } catch (Exception $e) {
        ob_clean();
        echo json_encode(array('error' => 'Exception: ' . $e->getMessage()));
    }
} else {
    ob_clean();
    echo json_encode(array('error' => 'Invalid request method'));
}

// End output buffering and flush
ob_end_flush();

function sanitizeInput($data) {
    if ($data === null) return '';
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function saveToDatabase($data, $servername, $username, $password, $dbname) {
    try {
        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        // Check connection
        if ($conn->connect_error) {
            return array('success' => false, 'message' => 'Connection failed: ' . $conn->connect_error);
        }
        
        // Set charset
        $conn->set_charset("utf8mb4");
        
        // Prepare SQL statement
        $sql = "INSERT INTO registrations (full_name, email, phone, dob, gender, course, address, registration_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            $conn->close();
            return array('success' => false, 'message' => 'Prepare failed: ' . $conn->error);
        }
        
        // Bind parameters
        $stmt->bind_param("sssssss", 
            $data['fullName'],
            $data['email'],
            $data['phone'],
            $data['dob'],
            $data['gender'],
            $data['course'],
            $data['address']
        );
        
        // Execute statement
        if ($stmt->execute()) {
            $insertId = $stmt->insert_id;
            $stmt->close();
            $conn->close();
            return array('success' => true, 'id' => $insertId);
        } else {
            $error = $stmt->error;
            $stmt->close();
            $conn->close();
            return array('success' => false, 'message' => 'Execute failed: ' . $error);
        }
        
    } catch (Exception $e) {
        return array('success' => false, 'message' => 'Exception: ' . $e->getMessage());
    }
}
?>

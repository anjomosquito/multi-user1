<?php
// Inside the form submission handling
if(isset($_POST['submit'])) {
    $medicine_name = $_POST['medicine_name'];
    $low_price = $_POST['low_price'];
    $med_price = $_POST['med_price'];
    $high_price = $_POST['high_price'];
    $quantity = $_POST['quantity'];
    $dosage = $_POST['dosage'];
    $exp_date = $_POST['exp_date'];
    
    // Check if medicine with same name exists
    $check_query = "SELECT * FROM medicines WHERE medicine_name = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $medicine_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        // Medicine exists, update quantity
        $update_query = "UPDATE medicines SET quantity = quantity + ? WHERE medicine_name = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("is", $quantity, $medicine_name);
        $stmt->execute();
        
        $_SESSION['success'] = "Medicine quantity updated successfully!";
    } else {
        // New medicine, insert new record
        $insert_query = "INSERT INTO medicines (medicine_name, low_price, med_price, high_price, quantity, dosage, exp_date, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'active')";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("sdddisd", $medicine_name, $low_price, $med_price, $high_price, $quantity, $dosage, $exp_date);
        $stmt->execute();
        
        $_SESSION['success'] = "New medicine added successfully!";
    }
    
    header("Location: inventory.php");
    exit();
} 
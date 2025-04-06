<?php
    session_start();
    include("connection.php");

    $errmessage = "";
    
    // Only process if the form was submitted
    if(isset($_POST['login'])) {
        // Prevent SQL injection by using prepared statements
        $email = $_POST['email'];
        $password = $_POST['password'];

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Use prepared statement to prevent SQL injection
        $sql = "SELECT email_address, account_password FROM accounts WHERE email_address = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            
            if($password == $row['account_password']) {
                // Store minimal info in session
                $_SESSION['loggedin'] = true;
                $_SESSION['email'] = $row['email_address'];
                $_SESSION['name'] = $row['account_name']; 
                
                // Don't store password in session for security
                
                // Redirect to main page
                header("Location: mainpage.php");
                exit(); // Important to exit after redirect
            } else {
                $_SESSION['error_message'] = "Incorrect password.";
                header("Location: loginpage.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Account with this email doesn't exist.";
            header("Location: loginpage.php");
            exit();
        }
    } else {
        // If someone accesses this page directly, redirect them
        header("Location: loginpage.php");
        exit();
    }
?>
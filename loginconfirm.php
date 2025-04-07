<?php
    session_start();
    include("connection.php");

    // Only process if the form was submitted
    if(isset($_POST['login'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $sql = "SELECT email_address, account_name, account_password, account_type FROM accounts WHERE email_address = ?";
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
                $_SESSION['password'] = $row['account_password'];
                $_SESSION['name'] = $row['account_name']; 
                $_SESSION['type'] = $row['account_type']; 
                unset($_SESSION['error_message']);
                
                // Redirect to main page
                header("Location: mainpage.php");
                exit();
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
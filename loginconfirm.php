<?php
    session_start();
    include("connection.php");

    $errmessage = "";
    
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if(isset($_POST['login'])) {
        $sql = "SELECT email_address, account_password FROM accounts WHERE email_address = '$email'";
        $result = $conn->query($sql);
        list($emailcon, $passcon) = mysqli_fetch_row($result);

        if(mysqli_num_rows($result) == 1) {
            if($password = $passcon) {
                $_SESSION['loggedin'] = true;
                $_SESSION['email'] = $email;
                $_SESSION['password'] = $password;
                header("location:mainpage.php");
            }
            else {
                $errmessage = "Incorrect password.";
                header("location:loginpage.php");
            }
        }
        else {
            $errmessage = "Account with this email doesn't exist.";
            header("location:loginpage.php");
        }

        
    }
?>
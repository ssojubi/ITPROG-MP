<?php
    session_start();
    include("connection.php");
    
    $email = $_SESSION['email'];
    $password = $_SESSION['password'];
    $oldpass = $_POST['oldpassword'];
    $newpass = $_POST['newpassword'];
    $confirmpass = $_POST['confirm-password'];

    if(isset($_POST['changepass'])) {
        $sql = "UPDATE accounts 
        SET account_password = '$newpass' 
        WHERE email_address = '$email'";

        if($oldpass != $password) {
            $_SESSION['error_message'] = "Incorrect password.";
            header("location:changepassword.php");
        }
        else if($oldpass == $newpass) {
            $_SESSION['error_message'] = "Old and new passwords are the same. Please choose a different password";
            header("location:changepassword.php");
        }
        else if($newpass != $confirmpass){
            $_SESSION['error_message'] = "Confirmation password did not match.";
            header("location:changepassword.php");
        }
        else {
            $result = $conn->query($sql);
            $_SESSION['password'] = $newpass;
            unset($_SESSION['error_message']);
            header("location:viewaccount.php");
        }
    }
?>
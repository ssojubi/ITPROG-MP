<?php
    session_start();
    include("connection.php");

    $errmessage = "";
    
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
            $errmessage = "Incorrect password.";
            header("location:changepassword.php");
        }
        else if($oldpass == $newpass) {
            $errmessage = "Old and new passwords are the same. Please choose a different password";
            header("location:changepassword.php");
        }
        else if($newpass != $confirmpass){
            $errmessage = "Confirmation password did not match.";
            header("location:changepassword.php");
        }
        else {
            $result = $conn->query($sql);
            $_SESSION['password'] = $newpass;
            header("location:viewaccount.php");
        }
    }
?>
<?php
    session_start();
    include("connection.php");
    
    $email = $_SESSION['email'];
    $password = $_SESSION['password'];
    $newname = $_POST['fullname'];
    $newbirthdate = $_POST['birthdate'];
    $newcontact = $_POST['contact'];
    $confirmpass = $_POST['password'];

    if(isset($_POST['edit'])) {
        $sql = "UPDATE accounts 
        SET account_name = '$newname', birth_date = '$newbirthdate', contact_number = '$newcontact' 
        WHERE email_address = '$email'";

        if($password == $confirmpass) {
            $result = $conn->query($sql);
            unset($_SESSION['error_message']);
            header("location:viewaccount.php");
        }
        else {
            $_SESSION['error_message'] = "Incorrect password.";
            header("location:editaccount.php");
        }
    }
?>
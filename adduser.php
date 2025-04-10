<?php
session_start();
include("connection.php");

// Check if user is admin, if not redirect
if(!isset($_SESSION['type']) || $_SESSION['type'] != 'admin') {
    header("location:mainpage.php");
    exit();
}

// Handle form submission
if(isset($_POST['add_user'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $birthdate = $_POST['birthdate'];
    $password = $_POST['password'];
    $type = $_POST['type'];
    
    // Validation
    $errors = [];
    
    if(empty($name)) {
        $errors[] = "Name is required";
    }
    
    if(empty($email)) {
        $errors[] = "Email is required";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email already exists
        $checkEmailQuery = "SELECT email_address FROM accounts WHERE email_address = ?";
        $stmt = $conn->prepare($checkEmailQuery);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if($stmt->num_rows > 0) {
            $errors[] = "Email already exists";
        }
        $stmt->close();
    }

    if(!empty($contact)) {
        // Validate Philippine phone number format
        if(!preg_match('/^(\+63|0)?9\d{9}$/', $contact)) {
            $errors[] = "Invalid Philippine mobile number format";
        }   
    }
    
    if(empty($birthdate)) {
        $errors[] = "Birth date is required";
    }
    
    if(empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no errors, add new user
    if(empty($errors)) {
        $insertQuery = "INSERT INTO accounts (email_address, account_name, birth_date, contact_number, account_password, account_type) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("sssiss", $email, $name, $birthdate, $contact, $password, $type);
        
        if($stmt->execute()) {
            $success = "User added successfully!";
        } else {
            $error = "Error adding user: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - The Premiere Club</title>
    <link rel="stylesheet" href="mainpage.css">
    <link rel="stylesheet" href="edit_user.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include("header.php"); ?>
    
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Add User</h1>
            <a href="manage_users.php" class="back-button">← Back to Users</a>
        </div>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="form-card">
            <form method="POST">
                <div class="form-section">
                    <h3 class="section-title">Basic Information</h3>
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        <?php if(isset($errors) && in_array("Name is required", $errors)): ?>
                            <span class="error-text">Name is required</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            <?php if(isset($errors) && in_array("Email is required", $errors)): ?>
                                <span class="error-text">Email is required</span>
                            <?php elseif(isset($errors) && in_array("Invalid email format", $errors)): ?>
                                <span class="error-text">Invalid email format</span>
                            <?php elseif(isset($errors) && in_array("Email already exists", $errors)): ?>
                                <span class="error-text">Email already exists</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact">Contact Number</label>
                            <input type="text" id="contact" name="contact" class="form-control" value="<?php echo isset($_POST['contact']) ? htmlspecialchars($_POST['contact']) : ''; ?>">
                            <?php if(isset($errors) && in_array("Contact number is required", $errors)): ?>
                                <span class="error-text">Contact number is required</span>
                            <?php elseif(isset($errors) && in_array("Invalid Philippine mobile number format", $errors)): ?>
                                <span class="error-text">Invalid Philippine mobile number format</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="birthdate">Birth Date</label>
                        <input type="date" id="birthdate" name="birthdate" class="form-control" value="<?php echo isset($_POST['birthdate']) ? htmlspecialchars($_POST['birthdate']) : ''; ?>">
                        <?php if(isset($errors) && in_array("Birth date is required", $errors)): ?>
                            <span class="error-text">Birth date is required</span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control">
                        <?php if(isset($errors) && in_array("Password is required", $errors)): ?>
                            <span class="error-text">Password is required</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="section-title">Account Settings</h3>
                    
                    <div class="form-group">
                        <label for="type">User Type</label>
                        <select id="type" name="type" class="form-control">
                            <option value="customer" selected>Customer</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-footer">
                    <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 The Premiere Club. All Rights Reserved.</p>
    </footer>
    <script>
    document.querySelector('form').addEventListener('submit', function(event) {
        var contactNumber = document.getElementById('contact').value;
        
        
        if(contactNumber) {
            // Validate Philippine phone number format
            var phonePattern = /^(\+63|0)?9\d{9}$/;
            if (!phonePattern.test(contactNumber)) {
                alert("Please enter a valid Philippine mobile number (e.g., +639XXXXXXXXX or 09XXXXXXXXX)");
                event.preventDefault();
                return;
            }
        }
});
</script>

</body>
</html>
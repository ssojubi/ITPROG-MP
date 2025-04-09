<?php
session_start();
include("connection.php");

// Check if user is admin, if not redirect
if(!isset($_SESSION['type']) || $_SESSION['type'] != 'admin') {
    header("location:mainpage.php");
    exit();
}

// Check if user email is provided
if(!isset($_GET['email']) || empty($_GET['email'])) {
    header("location:manage_users.php");
    exit();
}

$user_email = $_GET['email'];

// Handle form submission
if(isset($_POST['update_user'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $birthdate = $_POST['birthdate'];
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
        // Check if new email already exists and belongs to a different user
        if($email != $user_email) {
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
    }
    
    if(empty($contact)) {
        $errors[] = "Contact number is required";
    }
    
    if(empty($birthdate)) {
        $errors[] = "Birth date is required";
    }
    
    // If no errors, update user
    if(empty($errors)) {
        // First, check if email is changing
        if($email != $user_email) {
            // Email is the primary key, so we need to handle it differently
            // Create a new user with the new email and delete the old one
            $updateQuery = "INSERT INTO accounts (email_address, account_name, birth_date, contact_number, account_password, account_type) 
                            SELECT ?, ?, ?, ?, account_password, ? 
                            FROM accounts WHERE email_address = ?";
            
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("sssiss", $email, $name, $birthdate, $contact, $type, $user_email);
            
            if($stmt->execute()) {
                // Delete the old record
                $deleteQuery = "DELETE FROM accounts WHERE email_address = ?";
                $delStmt = $conn->prepare($deleteQuery);
                $delStmt->bind_param("s", $user_email);
                
                if($delStmt->execute()) {
                    $success = "User updated successfully!";
                    // Update the user_email variable for subsequent operations
                    $user_email = $email;
                } else {
                    $error = "Error updating user: " . $conn->error;
                }
                $delStmt->close();
            } else {
                $error = "Error updating user: " . $conn->error;
            }
            $stmt->close();
        } else {
            // No email change, simple update
            $updateQuery = "UPDATE accounts SET 
                            account_name = ?,
                            birth_date = ?,
                            contact_number = ?,
                            account_type = ?
                            WHERE email_address = ?";
            
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("ssiss", $name, $birthdate, $contact, $type, $email);
            
            if($stmt->execute()) {
                $success = "User updated successfully!";
            } else {
                $error = "Error updating user: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Reset password functionality
if(isset($_POST['reset_password'])) {
    // Generate a random password
    $new_password = substr(md5(rand()), 0, 8);
    
    $updatePasswordQuery = "UPDATE accounts SET account_password = ? WHERE email_address = ?";
    $stmt = $conn->prepare($updatePasswordQuery);
    $stmt->bind_param("ss", $new_password, $user_email);
    
    if($stmt->execute()) {
        $password_success = "Password has been reset. New password: " . $new_password;
    } else {
        $password_error = "Error resetting password: " . $conn->error;
    }
    $stmt->close();
}

// Get user data
$query = "SELECT email_address, account_name, birth_date, contact_number, account_password, account_type
          FROM accounts 
          WHERE email_address = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0) {
    header("location:manage_users.php");
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - The Premiere Club</title>
    <link rel="stylesheet" href="mainpage.css">
    <link rel="stylesheet" href="edit_user.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include("header.php"); ?>
    
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Edit User</h1>
            <a href="manage_users.php" class="back-button">← Back to Users</a>
        </div>
        
        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="user-meta">
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user_email); ?></p>
        </div>
        
        <div class="form-card">
            <form method="POST">
                <div class="form-section">
                    <h3 class="section-title">Basic Information</h3>
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['account_name']); ?>">
                        <?php if(isset($errors) && in_array("Name is required", $errors)): ?>
                            <span class="error-text">Name is required</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email_address']); ?>">
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
                            <input type="text" id="contact" name="contact" class="form-control" value="<?php echo htmlspecialchars($user['contact_number']); ?>">
                            <?php if(isset($errors) && in_array("Contact number is required", $errors)): ?>
                                <span class="error-text">Contact number is required</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="birthdate">Birth Date</label>
                        <input type="date" id="birthdate" name="birthdate" class="form-control" value="<?php echo htmlspecialchars($user['birth_date']); ?>">
                        <?php if(isset($errors) && in_array("Birth date is required", $errors)): ?>
                            <span class="error-text">Birth date is required</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="section-title">Account Settings</h3>
                    
                    <div class="form-group">
                        <label for="type">User Type</label>
                        <select id="type" name="type" class="form-control">
                            <option value="customer" <?php echo $user['account_type'] == 'customer' ? 'selected' : ''; ?>>Customer</option>
                            <option value="admin" <?php echo $user['account_type'] == 'admin' ? 'selected' : ''; ?>>Administrator</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-footer">
                    <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
        
        <div class="form-card">
            <h3 class="section-title">Password Management</h3>
            
            <?php if(isset($password_success)): ?>
                <div class="alert alert-success"><?php echo $password_success; ?></div>
            <?php endif; ?>
            
            <?php if(isset($password_error)): ?>
                <div class="alert alert-error"><?php echo $password_error; ?></div>
            <?php endif; ?>
            
            <p>Reset the user's password to a randomly generated secure password.</p>
            <p>Make sure to share this new password with the user as they will need it to login.</p>
            
            <form method="POST" onsubmit="return confirm('Are you sure you want to reset this user\'s password?');">
                <button type="submit" name="reset_password" class="btn btn-danger">Reset Password</button>
            </form>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 The Premiere Club. All Rights Reserved.</p>
    </footer>
</body>
</html>
<?php
session_start();
include("connection.php");

// Check if user is admin, if not redirect
if(!isset($_SESSION['type']) || $_SESSION['type'] != 'admin') {
    header("location:mainpage.php");
    exit();
}

// Handle add user action
if(isset($_POST['add_user'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $contact = $_POST['contact'];
    $birth_date = $_POST['birth_date'];
    $account_type = $_POST['account_type'];
    
    // Check if email already exists
    $checkEmailQuery = "SELECT COUNT(*) FROM accounts WHERE email_address = ?";
    $stmt = $conn->prepare($checkEmailQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($emailCount);
    $stmt->fetch();
    $stmt->close();
    
    if($emailCount > 0) {
        $addError = "Email address already exists. Please try a different email.";
    } else {
        // Add user
        $addQuery = "INSERT INTO accounts (email_address, account_name, account_password, contact_number, birth_date, account_type) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($addQuery);
        $stmt->bind_param("ssssss", $email, $name, $password, $contact, $birth_date, $account_type);
        
        if($stmt->execute()) {
            $addSuccess = "User added successfully!";
        } else {
            $addError = "Error adding user: " . $conn->error;
        }
        $stmt->close();
    }
}

// Handle delete user action
if(isset($_POST['delete_user'])) {
    $email = $_POST['user_email'];
    
    // Check if user has any bookings (adjust this query based on your booking_seats table structure)
    $checkBookingsQuery = "SELECT COUNT(*) FROM booking_seats WHERE customer_email = ?";
    $stmt = $conn->prepare($checkBookingsQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($bookingCount);
    $stmt->fetch();
    $stmt->close();
    
    if($bookingCount > 0) {
        $deleteError = "Cannot delete user with existing bookings. Please cancel their bookings first.";
    } else {
        // Delete user
        $deleteQuery = "DELETE FROM accounts WHERE email_address = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("s", $email);
        
        if($stmt->execute()) {
            $deleteSuccess = "User deleted successfully!";
        } else {
            $deleteError = "Error deleting user: " . $conn->error;
        }
        $stmt->close();
    }
}

// Handle search/filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$type_filter = isset($_GET['type']) ? $_GET['type'] : 'all';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Build the query
$query = "SELECT email_address, account_name, birth_date, contact_number, account_password, account_type 
          FROM accounts 
          WHERE 1=1 ";

$countQuery = "SELECT COUNT(*) FROM accounts WHERE 1=1 ";

// Add search condition if search is provided
if(!empty($search)) {
    $query .= "AND (account_name LIKE ? OR email_address LIKE ? OR contact_number LIKE ?) ";
    $countQuery .= "AND (account_name LIKE ? OR email_address LIKE ? OR contact_number LIKE ?) ";
}

// Add type filter condition
if($type_filter != 'all') {
    $query .= "AND account_type = ? ";
    $countQuery .= "AND account_type = ? ";
}

// Exclude current admin from the list
$query .= "AND email_address != ? ";
$countQuery .= "AND email_address != ? ";

// Add ordering and limit
$query .= "ORDER BY account_name ASC LIMIT ?, ?";

// Prepare and execute count query
$countStmt = $conn->prepare($countQuery);

// Bind parameters for count query
$bindParams = "";
$countParams = [];

if(!empty($search)) {
    $searchParam = "%$search%";
    $bindParams .= "sss";
    $countParams[] = $searchParam;
    $countParams[] = $searchParam;
    $countParams[] = $searchParam;
}

if($type_filter != 'all') {
    $bindParams .= "s";
    $countParams[] = $type_filter;
}

// Add current admin email
$bindParams .= "s";
$countParams[] = $_SESSION['email']; // Assuming this is set in session

// Dynamic parameter binding for count query
if(!empty($countParams)) {
    $countStmt->bind_param($bindParams, ...$countParams);
}

$countStmt->execute();
$countStmt->bind_result($total_records);
$countStmt->fetch();
$countStmt->close();

$total_pages = ceil($total_records / $records_per_page);

// Prepare and execute main query
$stmt = $conn->prepare($query);

// Bind parameters for main query
$bindParams = "";
$queryParams = [];

if(!empty($search)) {
    $searchParam = "%$search%";
    $bindParams .= "sss";
    $queryParams[] = $searchParam;
    $queryParams[] = $searchParam;
    $queryParams[] = $searchParam;
}

if($type_filter != 'all') {
    $bindParams .= "s";
    $queryParams[] = $type_filter;
}

// Add current admin email
$bindParams .= "s";
$queryParams[] = $_SESSION['email']; // Assuming this is set in session

// Add limit parameters
$bindParams .= "ii";
$queryParams[] = $offset;
$queryParams[] = $records_per_page;

// Dynamic parameter binding for main query
$stmt->bind_param($bindParams, ...$queryParams);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - The Premiere Club</title>
    <link href="manage_users.css" rel="stylesheet">
    <link href="mainpage.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">

</head>
<body>
    <?php include("header.php"); ?>
    
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">User Management</h1>
            <div class="header-actions">
                <a href="adduser.php" class="primary-button">Add User</a>
                <a href="viewaccount.php" class="back-button">← Back to Dashboard</a>
            </div>
        </div>
        
        <!-- Success/Error Messages -->
        <?php if(isset($addSuccess)): ?>
            <div class="alert alert-success"><?php echo $addSuccess; ?></div>
        <?php endif; ?>
        
        <?php if(isset($addError)): ?>
            <div class="alert alert-error"><?php echo $addError; ?></div>
        <?php endif; ?>
        
        <?php if(isset($deleteSuccess)): ?>
            <div class="alert alert-success"><?php echo $deleteSuccess; ?></div>
        <?php endif; ?>
        
        <?php if(isset($deleteError)): ?>
            <div class="alert alert-error"><?php echo $deleteError; ?></div>
        <?php endif; ?>
        
        <!-- Filters -->
        <div class="filters">
            <form action="" method="GET">
                <div class="filter-group">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" placeholder="Name, email, or phone" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                                
                <div class="filter-group">
                    <label for="type">User Type</label>
                    <select id="type" name="type">
                        <option value="all" <?php echo $type_filter == 'all' ? 'selected' : ''; ?>>All Types</option>
                        <option value="customer" <?php echo $type_filter == 'customer' ? 'selected' : ''; ?>>Customer</option>
                        <option value="admin" <?php echo $type_filter == 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                
                <div class="filter-buttons">
                    <button type="submit" class="primary-button">Filter</button>
                    <a href="manage_users.php" class="secondary-button">Reset</a>
                </div>
            </form>
        </div>
        
        <!-- User Table -->
        <?php if($result->num_rows > 0): ?>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Birth Date</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['account_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email_address']); ?></td>
                            <td><?php echo htmlspecialchars($row['contact_number']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['birth_date'])); ?></td>
                            <td>
                                <?php if($row['account_type'] == 'admin'): ?>
                                    <span class="user-type user-type-admin">Admin</span>
                                <?php else: ?>
                                    <span class="user-type user-type-regular">Customer</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="edit_user.php?email=<?php echo urlencode($row['email_address']); ?>" class="edit-button">Edit</a>
                                    <button class="delete-button" onclick="confirmDelete('<?php echo htmlspecialchars($row['email_address']); ?>', '<?php echo htmlspecialchars($row['account_name']); ?>')">Delete</button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
                <div class="pagination">
                    <?php if($page > 1): ?>
                        <a href="?page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $type_filter != 'all' ? '&type=' . urlencode($type_filter) : ''; ?>">First</a>
                        <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $type_filter != 'all' ? '&type=' . urlencode($type_filter) : ''; ?>">Prev</a>
                    <?php endif; ?>
                    
                    <?php
                    // Display a range of page numbers
                    $range = 2;
                    $start_page = max(1, $page - $range);
                    $end_page = min($total_pages, $page + $range);
                    
                    for($i = $start_page; $i <= $end_page; $i++): 
                    ?>
                        <?php if($i == $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $type_filter != 'all' ? '&type=' . urlencode($type_filter) : ''; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $type_filter != 'all' ? '&type=' . urlencode($type_filter) : ''; ?>">Next</a>
                        <a href="?page=<?php echo $total_pages; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $type_filter != 'all' ? '&type=' . urlencode($type_filter) : ''; ?>">Last</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="no-results">
                <p>No users found matching your criteria.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Confirm Delete</h3>
                <span class="close">&times;</span>
            </div>
            <p>Are you sure you want to delete the user <strong id="deleteUserName"></strong>?</p>
            <p>This action cannot be undone.</p>
            <div class="modal-actions">
                <button class="secondary-button" id="cancelDelete">Cancel</button>
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="user_email" id="deleteUserEmail">
                    <button type="submit" name="delete_user" class="delete-button">Delete</button>
                </form>
            </div>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2025 The Premiere Club. All Rights Reserved.</p>
    </footer>
    
    <script>
        // Delete confirmation modal
        const deleteModal = document.getElementById("deleteModal");
        const deleteCloseBtn = document.getElementsByClassName("close")[0];
        const cancelDeleteBtn = document.getElementById("cancelDelete");
        const deleteUserName = document.getElementById("deleteUserName");
        const deleteUserEmail = document.getElementById("deleteUserEmail");
        
        function confirmDelete(email, userName) {
            deleteModal.style.display = "block";
            deleteUserName.textContent = userName;
            deleteUserEmail.value = email;
        }
        
        deleteCloseBtn.onclick = function() {
            deleteModal.style.display = "none";
        }
        
        cancelDeleteBtn.onclick = function() {
            deleteModal.style.display = "none";
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target == deleteModal) {
                deleteModal.style.display = "none";
            }
            if (event.target == addUserModal) {
                addUserModal.style.display = "none";
            }
        }
    </script>
</body>
</html>
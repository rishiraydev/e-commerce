<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$user = getUser($user_id);
$page_title = 'My Profile - ' . SITE_NAME;

// Process profile update form
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $state = isset($_POST['state']) ? trim($_POST['state']) : '';
    $zip_code = isset($_POST['zip_code']) ? trim($_POST['zip_code']) : '';
    $country = isset($_POST['country']) ? trim($_POST['country']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    
    // Validate email
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        // Check if email already exists (except for current user)
        $db = new Database();
        $db->query("SELECT id FROM users WHERE email = :email AND id != :id");
        $db->bind(':email', $email);
        $db->bind(':id', $user_id);
        $existing_user = $db->single();
        
        if($existing_user) {
            $error = 'Email already exists.';
        } else {
            // Update profile
            $db->query("UPDATE users SET 
                        first_name = :first_name,
                        last_name = :last_name,
                        email = :email,
                        address = :address,
                        city = :city,
                        state = :state,
                        zip_code = :zip_code,
                        country = :country,
                        phone = :phone
                        WHERE id = :id");
            
            $db->bind(':first_name', $first_name);
            $db->bind(':last_name', $last_name);
            $db->bind(':email', $email);
            $db->bind(':address', $address);
            $db->bind(':city', $city);
            $db->bind(':state', $state);
            $db->bind(':zip_code', $zip_code);
            $db->bind(':country', $country);
            $db->bind(':phone', $phone);
            $db->bind(':id', $user_id);
            
            if($db->execute()) {
                $success = 'Profile updated successfully.';
                $user = getUser($user_id); // Refresh user data
            } else {
                $error = 'Failed to update profile. Please try again.';
            }
        }
    }
}

// Process password change form
if(isset($_POST['change_password'])) {
    $current_password = isset($_POST['current_password']) ? trim($_POST['current_password']) : '';
    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
    
    if(empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $password_error = 'All password fields are required.';
    } elseif($new_password !== $confirm_password) {
        $password_error = 'New passwords do not match.';
    } elseif(strlen($new_password) < 6) {
        $password_error = 'Password must be at least 6 characters.';
    } elseif(!password_verify($current_password, $user->password)) {
        $password_error = 'Current password is incorrect.';
    } else {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $db = new Database();
        $db->query("UPDATE users SET password = :password WHERE id = :id");
        $db->bind(':password', $hashed_password);
        $db->bind(':id', $user_id);
        
        if($db->execute()) {
            $password_success = 'Password changed successfully.';
        } else {
            $password_error = 'Failed to change password. Please try again.';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-md-8">
        <h2>My Profile</h2>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php elseif(isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Personal Information</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?php echo htmlspecialchars($user->first_name ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?php echo htmlspecialchars($user->last_name ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" 
                                   value="<?php echo htmlspecialchars($user->username); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user->email); ?>" required>
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address" 
                                   value="<?php echo htmlspecialchars($user->address ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" 
                                   value="<?php echo htmlspecialchars($user->city ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="state" class="form-label">State/Province</label>
                            <input type="text" class="form-control" id="state" name="state" 
                                   value="<?php echo htmlspecialchars($user->state ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="zip_code" class="form-label">Zip/Postal Code</label>
                            <input type="text" class="form-control" id="zip_code" name="zip_code" 
                                   value="<?php echo htmlspecialchars($user->zip_code ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="country" name="country" 
                                   value="<?php echo htmlspecialchars($user->country ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user->phone ?? ''); ?>">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>Change Password</h5>
            </div>
            <div class="card-body">
                <?php if(isset($password_error)): ?>
                    <div class="alert alert-danger"><?php echo $password_error; ?></div>
                <?php elseif(isset($password_success)): ?>
                    <div class="alert alert-success"><?php echo $password_success; ?></div>
                <?php endif; ?>
                
                <form method="post">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Account Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($user->created_at)); ?></p>
                
                <?php if(isAdmin()): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-shield-alt"></i> You have administrator privileges.
                    </div>
                <?php endif; ?>
                
                <a href="orders.php" class="btn btn-outline-primary w-100 mb-2">My Orders</a>
                <a href="logout.php" class="btn btn-outline-danger w-100">Logout</a>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
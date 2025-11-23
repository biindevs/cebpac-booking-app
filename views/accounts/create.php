<?php 
include "../templates/header.php"; 
require_once '../../controllers/AccountController.php';

$controller = new AccountController();
$result = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->create(
        $_POST['email'],
        $_POST['fullname'],
        $_POST['status'],
        $_POST['travel_funds'] ?? 0
    );
    
    if (isset($result['success'])) {
        header("Location: index.php");
        exit;
    }
}
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="display-6">Add New CebuPac Account</h1>
        <p class="text-muted">Register a new email account for tracking flight bookings</p>
    </div>
</div>

<?php if ($result && isset($result['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($result['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card card-custom">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Account Registration Form</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter CebuPac email" required>
                        <small class="text-muted">This should be your registered CebuPac booking email</small>
                    </div>

                    <div class="mb-3">
                        <label for="fullname" class="form-label fw-bold">Full Name</label>
                        <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Enter your full name" required>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label fw-bold">Account Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option selected disabled>Select status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="travel_funds" class="form-label fw-bold">Travel Funds Balance (PHP)</label>
                        <input type="number" class="form-control" id="travel_funds" name="travel_funds" placeholder="0.00" step="0.01" min="0" value="0.00" required>
                        <small class="text-muted">Initial travel funds balance for this account. This will be deducted when confirming bookings.</small>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success btn-lg">Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="alert alert-info" role="alert">
            <h5>Important Information</h5>
            <ul class="mb-0">
                <li>Email must be a valid CebuPac registered account</li>
                <li>Full name is required for identification</li>
                <li>Travel Funds will be deducted when you confirm bookings</li>
                <li>You can update account details and funds anytime</li>
                <li>Active accounts will be monitored for new bookings</li>
            </ul>
        </div>
    </div>
</div>

<?php include "../templates/footer.php"; ?>

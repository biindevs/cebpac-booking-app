<?php 
include "../templates/header.php"; 
require_once '../../controllers/AccountController.php';

$controller = new AccountController();
$result = null;
$account = null;

// Get account ID from URL
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$account = $controller->show($_GET['id']);

if (isset($account['error'])) {
    $error = $account['error'];
    $account = null;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $account) {
    $result = $controller->update(
        $_GET['id'],
        $_POST['fullname'],
        $_POST['status'],
        $_POST['travel_funds'] ?? 0
    );
    
    if (isset($result['success'])) {
        header("Location: show.php?id=" . $_GET['id']);
        exit;
    }
}
?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php else: ?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="display-6">Edit CebuPac Account</h1>
        <p class="text-muted">Update account information and settings</p>
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
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">Account Edit Form</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($account['email']); ?>" disabled>
                        <small class="text-muted">Email address cannot be changed</small>
                    </div>

                    <div class="mb-3">
                        <label for="fullname" class="form-label fw-bold">Full Name</label>
                        <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($account['full_name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label fw-bold">Account Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active" <?php echo $account['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $account['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="travel_funds" class="form-label fw-bold">Travel Funds Balance (PHP)</label>
                        <input type="number" class="form-control" id="travel_funds" name="travel_funds" value="<?php echo $account['travel_funds'] ?? 0; ?>" step="0.01" min="0" required>
                        <small class="text-muted">Current balance: <strong>₱<?php echo number_format($account['travel_funds'] ?? 0, 2); ?></strong></small>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-warning btn-lg">Update Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-custom">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Account Summary</h5>
            </div>
            <div class="card-body">
                <p><strong>Email:</strong> <?php echo htmlspecialchars($account['email']); ?></p>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($account['full_name']); ?></p>
                <p><strong>Status:</strong> 
                    <?php if ($account['status'] === 'active'): ?>
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-warning">Inactive</span>
                    <?php endif; ?>
                </p>
                <p><strong>Total Bookings:</strong> <?php echo $account['total_bookings'] ?? 0; ?></p>
                <p><strong>Registration Date:</strong> <?php echo date('M d, Y', strtotime($account['registration_date'])); ?></p>
                <hr>
                <p class="text-muted">Account ID: #<?php echo $account['id']; ?></p>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<?php include "../templates/footer.php"; ?>

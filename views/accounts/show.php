<?php 
include "../templates/header.php"; 
require_once '../../controllers/AccountController.php';
require_once '../../controllers/BookingController.php';

$accountController = new AccountController();
$bookingController = new BookingController();

// Get account ID from URL
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$account = $accountController->show($_GET['id']);

if (isset($account['error'])) {
    $error = $account['error'];
    $account = null;
} else {
    $bookings = $bookingController->getByAccount($_GET['id']);
}

// Handle delete booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_booking_id'])) {
    $result = $bookingController->delete($_POST['delete_booking_id']);
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
        <h1 class="display-6">Account Details: <?php echo htmlspecialchars($account['full_name']); ?></h1>
        <p class="text-muted">View all bookings associated with this account</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="index.php" class="btn btn-secondary">Back to Accounts</a>
        <a href="edit.php?id=<?php echo $account['id']; ?>" class="btn btn-warning">Edit Account</a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card card-custom">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Account Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($account['email']); ?></p>
                        <p><strong>Full Name:</strong> <?php echo htmlspecialchars($account['full_name']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Status:</strong> 
                            <?php if ($account['status'] === 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Inactive</span>
                            <?php endif; ?>
                        </p>
                        <p><strong>Registration Date:</strong> <?php echo date('M d, Y', strtotime($account['registration_date'])); ?></p>
                        <p><strong>Total Bookings:</strong> <span class="badge bg-primary"><?php echo $account['total_bookings'] ?? 0; ?></span></p>
                    </div>
                </div>
                <hr>
                <p><strong>Travel Funds Balance:</strong></p>
                <span class="badge bg-info" style="font-size: 1rem; padding: 0.75rem 1.5rem;">₱<?php echo number_format($account['travel_funds'], 2); ?></span>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Associated Bookings</h4>
            <a href="../bookings/create.php?account_id=<?php echo $account['id']; ?>" class="btn btn-success btn-sm">Add New Booking</a>
        </div>
        <div class="card card-custom">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Flight Bookings</h5>
            </div>
            <div class="card-body">
                <?php if (empty($bookings) || isset($bookings['error'])): ?>
                    <div class="alert alert-info">No bookings found for this account. <a href="../bookings/create.php?account_id=<?php echo $account['id']; ?>">Create one now</a></div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Route</th>
                                    <th>Departure Date</th>
                                    <th>Passengers</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($booking['booking_reference']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['departure_city']); ?> → <?php echo htmlspecialchars($booking['arrival_city']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking['departure_date'])); ?></td>
                                    <td><?php echo $booking['number_of_passengers']; ?></td>
                                    <td>₱<?php echo number_format($booking['total_price'], 2); ?></td>
                                    <td>
                                        <?php 
                                        $statusClass = 'bg-secondary';
                                        if ($booking['status'] === 'confirmed') $statusClass = 'bg-success';
                                        elseif ($booking['status'] === 'pending') $statusClass = 'bg-warning';
                                        elseif ($booking['status'] === 'cancelled') $statusClass = 'bg-danger';
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($booking['status']); ?></span>
                                    </td>
                                    <td>
                                        <a href="../bookings/view.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-info">View</a>
                                        <a href="../bookings/edit.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="showDeleteModal(<?php echo $booking['id']; ?>, 'booking', '<?php echo htmlspecialchars($booking['booking_reference']); ?>')">Delete</button>
                                        <form id="deleteForm_<?php echo $booking['id']; ?>" method="POST" style="display:none;">
                                            <input type="hidden" name="delete_booking_id" value="<?php echo $booking['id']; ?>">
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<?php include "../templates/footer.php"; ?>

<?php 
include "../templates/header.php"; 
require_once '../../controllers/BookingController.php';
require_once '../../models/Account.php';

$bookingController = new BookingController();
$accountModel = new Account();

// Get booking ID from URL
$booking_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$booking_id) {
    header("Location: ../accounts/index.php");
    exit;
}

// Get booking details
$booking = $bookingController->show($booking_id);

if (isset($booking['error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($booking['error']) . '</div>';
    include "../templates/footer.php";
    exit;
}

// Get account details
$account = $accountModel->getById($booking['account_id']);

// Decode passenger information
$passengers = [];
if (!empty($booking['passengers_info'])) {
    $passengers = json_decode($booking['passengers_info'], true);
}
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="display-6">Booking Details</h1>
        <p class="text-muted">View complete booking information</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="javascript:history.back()" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Booking Information Card -->
        <div class="card card-custom mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Booking Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Booking Reference:</strong>
                        <p><?php echo htmlspecialchars($booking['booking_reference']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Account:</strong>
                        <p><?php echo htmlspecialchars($account['full_name'] . ' (' . $account['email'] . ')'); ?></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <p>
                            <span class="badge bg-<?php echo $booking['status'] === 'confirmed' ? 'success' : ($booking['status'] === 'cancelled' ? 'danger' : 'warning'); ?>">
                                <?php echo ucfirst(htmlspecialchars($booking['status'])); ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong>Booking Date:</strong>
                        <p><?php echo date('M d, Y H:i', strtotime($booking['booking_date'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flight Details Card -->
        <div class="card card-custom mb-4">
            <div class="card-header bg-info text-dark">
                <h5 class="mb-0">Flight Details</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Departure City:</strong>
                        <p><?php echo htmlspecialchars($booking['departure_city']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Arrival City:</strong>
                        <p><?php echo htmlspecialchars($booking['arrival_city']); ?></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Departure Date:</strong>
                        <p><?php echo date('M d, Y', strtotime($booking['departure_date'])); ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Return Date:</strong>
                        <p><?php echo !empty($booking['return_date']) ? date('M d, Y', strtotime($booking['return_date'])) : '<em class="text-muted">One-way</em>'; ?></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Number of Passengers:</strong>
                        <p><?php echo htmlspecialchars($booking['number_of_passengers']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Total Price:</strong>
                        <p><strong>₱<?php echo number_format($booking['total_price'], 2); ?></strong></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Passengers Card -->
        <?php if (!empty($passengers)): ?>
        <div class="card card-custom mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">Passenger List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($passengers as $index => $passenger): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($passenger['title']); ?></td>
                                <td><?php echo htmlspecialchars($passenger['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($passenger['last_name']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Notes and Documents Card -->
        <div class="card card-custom mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Notes & Documents</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($booking['notes'])): ?>
                <div class="mb-3">
                    <strong>Notes:</strong>
                    <p><?php echo nl2br(htmlspecialchars($booking['notes'])); ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($booking['itinerary_pdf'])): ?>
                <div class="mb-3">
                    <strong>Itinerary PDF:</strong>
                    <p>
                        <a href="../../public/uploads/<?php echo htmlspecialchars($booking['itinerary_pdf']); ?>" target="_blank" class="btn btn-primary btn-sm">
                            📄 View PDF
                        </a>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-grid gap-2 d-md-flex justify-content-md-between mb-4">
            <a href="javascript:history.back()" class="btn btn-secondary">← Back</a>
            <a href="edit.php?id=<?php echo $booking['id']; ?>" class="btn btn-warning">✏️ Edit Booking</a>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <div class="alert alert-info" role="alert">
            <h5>Booking Summary</h5>
            <ul class="mb-0 small">
                <li><strong>Reference:</strong> <?php echo htmlspecialchars($booking['booking_reference']); ?></li>
                <li><strong>Status:</strong> <?php echo ucfirst(htmlspecialchars($booking['status'])); ?></li>
                <li><strong>Passengers:</strong> <?php echo htmlspecialchars($booking['number_of_passengers']); ?></li>
                <li><strong>Total:</strong> ₱<?php echo number_format($booking['total_price'], 2); ?></li>
                <li><strong>Booked:</strong> <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></li>
            </ul>
        </div>
    </div>
</div>

<?php include "../templates/footer.php"; ?>

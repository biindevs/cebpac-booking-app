<?php 
include "../templates/header.php"; 
require_once '../../controllers/BookingController.php';

$bookingController = new BookingController();
$result = null;
$booking = null;

// Get booking ID from URL
if (!isset($_GET['id'])) {
    header("Location: ../accounts/index.php");
    exit;
}

$booking = $bookingController->show($_GET['id']);

if (isset($booking['error'])) {
    $error = $booking['error'];
    $booking = null;
}

// Sanitize helper function
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $booking) {
    $itinerary_pdf = $booking['itinerary_pdf']; // Keep existing PDF by default
    
    // Handle file upload
    if (isset($_FILES['itinerary_pdf']) && $_FILES['itinerary_pdf']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['itinerary_pdf'];
        $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        if (strtolower($file_ext) !== 'pdf') {
            $result = ['error' => 'Only PDF files are allowed'];
        } else if ($file['size'] > 5000000) {
            $result = ['error' => 'File size must be less than 5MB'];
        } else {
            $upload_dir = __DIR__ . '/../../public/uploads/';
            $filename = 'itinerary_' . time() . '_' . uniqid() . '.pdf';
            $upload_path = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $itinerary_pdf = $filename;
            } else {
                $result = ['error' => 'Failed to upload file'];
            }
        }
    }
    
    if (!isset($result) || !isset($result['error'])) {
        $passengers_info = [];
        if (isset($_POST['number_of_passengers']) && $_POST['number_of_passengers'] > 0) {
            for ($i = 1; $i <= $_POST['number_of_passengers']; $i++) {
                if (isset($_POST["passenger_{$i}_title"]) && isset($_POST["passenger_{$i}_first"]) && isset($_POST["passenger_{$i}_last"])) {
                    $passengers_info[] = [
                        'title' => sanitize($_POST["passenger_{$i}_title"]),
                        'first_name' => sanitize($_POST["passenger_{$i}_first"]),
                        'last_name' => sanitize($_POST["passenger_{$i}_last"])
                    ];
                }
            }
        }
        
        $result = $bookingController->update(
            $_GET['id'],
            $_POST['departure_city'],
            $_POST['arrival_city'],
            $_POST['departure_date'],
            $_POST['return_date'] ?? null,
            $_POST['number_of_passengers'],
            $_POST['price'],
            $_POST['status'],
            $_POST['notes'] ?? null,
            $itinerary_pdf,
            json_encode($passengers_info)
        );
        
        if (isset($result['success'])) {
            header("Location: ../accounts/show.php?id=" . $booking['account_id']);
            exit;
        }
    }
}

// Handle delete
$delete_result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $delete_result = $bookingController->delete($_GET['id']);
    if (isset($delete_result['success'])) {
        header("Location: ../accounts/show.php?id=" . $booking['account_id']);
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
        <h1 class="display-6">Edit Flight Booking</h1>
        <p class="text-muted">Update booking details and information</p>
    </div>
</div>

<?php if ($result && isset($result['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($result['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card card-custom">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">Edit Booking Form</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-bold">CebuPac Account</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($booking['full_name'] . ' - ' . $booking['email']); ?>" disabled>
                            <small class="text-muted">Cannot change account for existing booking</small>
                        </div>
                        <div class="col-md-6">
                            <label for="booking_reference" class="form-label fw-bold">Booking Reference ID</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($booking['booking_reference']); ?>" disabled>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="departure_city" class="form-label fw-bold">Departure City</label>
                            <input type="text" class="form-control" id="departure_city" name="departure_city" value="<?php echo htmlspecialchars($booking['departure_city']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="arrival_city" class="form-label fw-bold">Arrival City</label>
                            <input type="text" class="form-control" id="arrival_city" name="arrival_city" value="<?php echo htmlspecialchars($booking['arrival_city']); ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="departure_date" class="form-label fw-bold">Departure Date</label>
                            <input type="date" class="form-control" id="departure_date" name="departure_date" value="<?php echo $booking['departure_date']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="return_date" class="form-label fw-bold">Return Date (Optional)</label>
                            <input type="date" class="form-control" id="return_date" name="return_date" value="<?php echo $booking['return_date'] ?? ''; ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="number_of_passengers" class="form-label fw-bold">Number of Passengers</label>
                            <input type="number" class="form-control" id="number_of_passengers" name="number_of_passengers" value="<?php echo $booking['number_of_passengers']; ?>" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label for="price" class="form-label fw-bold">Total Price (PHP)</label>
                            <input type="number" class="form-control" id="price" name="price" value="<?php echo $booking['total_price']; ?>" step="0.01" required>
                        </div>
                        <div class="col-md-4">
                            <label for="status" class="form-label fw-bold">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label fw-bold">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($booking['notes'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="itinerary_pdf" class="form-label fw-bold">Upload Itinerary (PDF)</label>
                        <input type="file" class="form-control" id="itinerary_pdf" name="itinerary_pdf" accept=".pdf">
                        <small class="text-muted">Optional: Upload or replace flight itinerary PDF. Max file size: 5MB</small>
                        <?php if (!empty($booking['itinerary_pdf'])): ?>
                            <div class="mt-2">
                                <strong>Current PDF:</strong>
                                <a href="../../public/uploads/<?php echo htmlspecialchars($booking['itinerary_pdf']); ?>" target="_blank" class="btn btn-sm btn-info">📄 View Current PDF</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Passenger Details Section -->
                    <div class="card card-custom mt-4 mb-3">
                        <div class="card-header bg-info text-dark">
                            <h5 class="mb-0">Passenger Details</h5>
                        </div>
                        <div class="card-body">
                            <div id="passengers-container">
                                <!-- Passenger fields will be generated here -->
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                        <a href="../accounts/show.php?id=<?php echo $booking['account_id']; ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-warning btn-lg">Update Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-custom">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Booking Summary</h5>
            </div>
            <div class="card-body">
                <p><strong>Booking ID:</strong> <?php echo htmlspecialchars($booking['booking_reference']); ?></p>
                <p><strong>Account:</strong> <?php echo htmlspecialchars($booking['full_name']); ?></p>
                <p><strong>Route:</strong> <?php echo htmlspecialchars(substr($booking['departure_city'], -4) . ' → ' . substr($booking['arrival_city'], -4)); ?></p>
                <p><strong>Departure:</strong> <?php echo date('M d, Y', strtotime($booking['departure_date'])); ?></p>
                <p><strong>Passengers:</strong> <?php echo $booking['number_of_passengers']; ?></p>
                <p><strong>Price:</strong> ₱<?php echo number_format($booking['total_price'], 2); ?></p>
                <p><strong>Status:</strong> 
                    <?php 
                    $statusClass = 'bg-secondary';
                    if ($booking['status'] === 'confirmed') $statusClass = 'bg-success';
                    elseif ($booking['status'] === 'pending') $statusClass = 'bg-warning';
                    elseif ($booking['status'] === 'cancelled') $statusClass = 'bg-danger';
                    ?>
                    <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($booking['status']); ?></span>
                </p>
                <hr>
                <p class="text-muted small">Created: <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></p>
                <p class="text-muted small">Last Updated: <?php echo date('M d, Y', strtotime($booking['last_updated'])); ?></p>
            </div>
        </div>

        <div class="alert alert-warning mt-3">
            <h6>Danger Zone</h6>
            <button type="button" class="btn btn-danger btn-sm w-100" onclick="showDeleteModal(<?php echo $_GET['id']; ?>, 'booking', '<?php echo htmlspecialchars($booking['booking_reference']); ?>')">Delete This Booking</button>
            <form id="deleteForm_<?php echo $_GET['id']; ?>" method="POST" style="display:none;">
                <input type="hidden" name="confirm_delete" value="1">
            </form>
        </div>
    </div>
</div>

<script>
    // Decode existing passenger data
    const existingPassengers = <?php echo !empty($booking['passengers_info']) ? $booking['passengers_info'] : '[]'; ?>;
    
    // Generate passenger fields based on number of passengers
    function generatePassengerFields() {
        const numPassengers = parseInt(document.getElementById('number_of_passengers').value) || 1;
        const container = document.getElementById('passengers-container');
        container.innerHTML = '';
        
        for (let i = 1; i <= numPassengers; i++) {
            const passenger = existingPassengers[i - 1] || { title: '', first_name: '', last_name: '' };
            const passengerDiv = document.createElement('div');
            passengerDiv.className = 'row mb-3 p-3 border rounded bg-light';
            passengerDiv.innerHTML = `
                <h6 class="col-12 mb-3">Passenger ${i}</h6>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Title</label>
                    <select class="form-select" name="passenger_${i}_title" required>
                        <option value="">Select</option>
                        <option value="Mr" ${passenger.title === 'Mr' ? 'selected' : ''}>Mr</option>
                        <option value="Ms" ${passenger.title === 'Ms' ? 'selected' : ''}>Ms</option>
                        <option value="Mrs" ${passenger.title === 'Mrs' ? 'selected' : ''}>Mrs</option>
                        <option value="Dr" ${passenger.title === 'Dr' ? 'selected' : ''}>Dr</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold">First Name</label>
                    <input type="text" class="form-control" name="passenger_${i}_first" placeholder="First name" value="${passenger.first_name}" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold">Last Name</label>
                    <input type="text" class="form-control" name="passenger_${i}_last" placeholder="Last name" value="${passenger.last_name}" required>
                </div>
            `;
            container.appendChild(passengerDiv);
        }
    }
    
    document.getElementById('number_of_passengers').addEventListener('change', generatePassengerFields);
    
    // Initialize on page load
    generatePassengerFields();
</script>

<?php endif; ?>

<?php include "../templates/footer.php"; ?>

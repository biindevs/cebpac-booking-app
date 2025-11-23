<?php 
include "../templates/header.php"; 
require_once '../../controllers/BookingController.php';
require_once '../../models/Account.php';

$bookingController = new BookingController();
$accountModel = new Account();
$result = null;

// Get account ID if passed in URL
$selected_account_id = isset($_GET['account_id']) ? $_GET['account_id'] : null;

// Get all accounts for dropdown
$accountsResult = $accountModel->getAll();
$accounts = [];
while ($row = $accountsResult->fetch_assoc()) {
    $accounts[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itinerary_pdf = null;
    
    // Handle file upload
    if (isset($_FILES['itinerary_pdf']) && $_FILES['itinerary_pdf']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['itinerary_pdf'];
        $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        // Validate file type
        if (strtolower($file_ext) !== 'pdf') {
            $result = ['error' => 'Only PDF files are allowed'];
        } else if ($file['size'] > 5000000) { // 5MB limit
            $result = ['error' => 'File size must be less than 5MB'];
        } else {
            // Generate unique filename
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
    
    // Only proceed with booking creation if no file error occurred
    if (!isset($result) || !isset($result['error'])) {
        // Collect passenger information
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
        
        $result = $bookingController->create(
            $_POST['account_id'],
            $_POST['booking_reference'],
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
            header("Location: ../accounts/show.php?id=" . $_POST['account_id']);
            exit;
        }
    }
}

// Sanitize helper function
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="display-6">Add New Flight Booking</h1>
        <p class="text-muted">Create a new flight booking record for tracking</p>
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
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">New Booking Form</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="account_id" class="form-label fw-bold">Select CebuPac Account</label>
                            <select class="form-select" id="account_id" name="account_id" required>
                                <option selected disabled>Choose an account...</option>
                                <?php foreach ($accounts as $account): ?>
                                    <option value="<?php echo $account['id']; ?>" <?php echo ($selected_account_id == $account['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($account['full_name'] . ' - ' . $account['email']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="booking_reference" class="form-label fw-bold">Booking Reference ID</label>
                            <input type="text" class="form-control" id="booking_reference" name="booking_reference" placeholder="e.g., BK001" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="departure_city" class="form-label fw-bold">Departure City</label>
                            <input type="text" class="form-control" id="departure_city" name="departure_city" placeholder="e.g., Cebu (CEB)" required>
                        </div>
                        <div class="col-md-6">
                            <label for="arrival_city" class="form-label fw-bold">Arrival City</label>
                            <input type="text" class="form-control" id="arrival_city" name="arrival_city" placeholder="e.g., Manila (MNL)" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="departure_date" class="form-label fw-bold">Departure Date</label>
                            <input type="date" class="form-control" id="departure_date" name="departure_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="return_date" class="form-label fw-bold">Return Date (Optional)</label>
                            <input type="date" class="form-control" id="return_date" name="return_date">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="number_of_passengers" class="form-label fw-bold">Number of Passengers</label>
                            <input type="number" class="form-control" id="number_of_passengers" name="number_of_passengers" value="1" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label for="price" class="form-label fw-bold">Total Price (PHP)</label>
                            <input type="number" class="form-control" id="price" name="price" placeholder="0.00" step="0.01" required>
                        </div>
                        <div class="col-md-4">
                            <label for="status" class="form-label fw-bold">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="confirmed">Confirmed</option>
                                <option value="pending" selected>Pending</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label fw-bold">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Add any booking notes or special requests..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="itinerary_pdf" class="form-label fw-bold">Upload Itinerary (PDF)</label>
                        <input type="file" class="form-control" id="itinerary_pdf" name="itinerary_pdf" accept=".pdf" placeholder="Select a PDF file...">
                        <small class="text-muted">Optional: Upload flight itinerary PDF. Max file size: 5MB</small>
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
                        <a href="../accounts/index.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success btn-lg">Create Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="alert alert-info" role="alert">
            <h5>Booking Tips</h5>
            <ul class="mb-0 small">
                <li>Select the correct CebuPac account</li>
                <li>Use IATA codes for cities</li>
                <li>Departure date is required</li>
                <li>Return date is optional (one-way trips)</li>
                <li>Enter all passenger details accurately</li>
            </ul>
        </div>
    </div>
</div>

<script>
    // Generate passenger fields based on number of passengers
    document.getElementById('number_of_passengers').addEventListener('change', function() {
        const numPassengers = parseInt(this.value) || 1;
        const container = document.getElementById('passengers-container');
        container.innerHTML = '';
        
        for (let i = 1; i <= numPassengers; i++) {
            const passengerDiv = document.createElement('div');
            passengerDiv.className = 'row mb-3 p-3 border rounded bg-light';
            passengerDiv.innerHTML = `
                <h6 class="col-12 mb-3">Passenger ${i}</h6>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Title</label>
                    <select class="form-select" name="passenger_${i}_title" required>
                        <option value="">Select</option>
                        <option value="Mr">Mr</option>
                        <option value="Ms">Ms</option>
                        <option value="Mrs">Mrs</option>
                        <option value="Dr">Dr</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold">First Name</label>
                    <input type="text" class="form-control" name="passenger_${i}_first" placeholder="First name" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold">Last Name</label>
                    <input type="text" class="form-control" name="passenger_${i}_last" placeholder="Last name" required>
                </div>
            `;
            container.appendChild(passengerDiv);
        }
    });
    
    // Initialize on page load
    document.getElementById('number_of_passengers').dispatchEvent(new Event('change'));
</script>

<?php include "../templates/footer.php"; ?>

<?php 
include "../templates/header.php"; 
require_once '../../controllers/AccountController.php';
require_once '../../models/Booking.php';

$controller = new AccountController();
$accounts = $controller->index();

// Get flight statistics for the graph
$bookingModel = new Booking();
$flightStats = $bookingModel->getFlightStatistics();
$completedCount = (int)$flightStats['completed_count'];
$confirmedCount = (int)$flightStats['confirmed_count'];

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $result = $controller->delete($_POST['delete_id']);
    if (isset($result['success'])) {
        header("Location: index.php");
        exit;
    }
}
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="display-6">CebuPac Email Accounts</h1>
        <p class="text-muted">Manage all your registered email accounts for booking tracking</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="create.php" class="btn btn-success btn-lg">
            <i class="bi bi-plus-circle"></i> Add New Account
        </a>
    </div>
</div>

<?php if (isset($result) && isset($result['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($result['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Flight Statistics Graph -->
<div class="card card-custom mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Flight Statistics</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <canvas id="flightStatsChart" height="100"></canvas>
            </div>
            <div class="col-md-4 d-flex align-items-center">
                <div class="w-100">
                    <div class="mb-3 p-3 rounded" style="background: rgba(37, 99, 235, 0.1); border-left: 4px solid #2563eb;">
                        <h6 class="mb-1 text-muted">Completed Flights</h6>
                        <h3 class="mb-0" style="color: #2563eb;"><?php echo $completedCount; ?></h3>
                    </div>
                    <div class="p-3 rounded" style="background: rgba(22, 163, 74, 0.1); border-left: 4px solid #16a34a;">
                        <h6 class="mb-1 text-muted">Confirmed Flights</h6>
                        <h3 class="mb-0" style="color: #16a34a;"><?php echo $confirmedCount; ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-custom mb-5">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Registered Accounts (<?php echo count($accounts); ?>)</h5>
    </div>
    <div class="card-body">
        <?php if (empty($accounts) || isset($accounts['error'])): ?>
            <div class="alert alert-info">No accounts found. <a href="create.php">Create one now</a></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Email Address</th>
                            <th>Full Name</th>
                            <th>Travel Funds</th>
                            <th>Registration Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accounts as $index => $account): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($account['email']); ?></td>
                            <td><?php echo htmlspecialchars($account['full_name']); ?></td>
                            <td><span class="badge bg-info">₱<?php echo number_format($account['travel_funds'], 2); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($account['registration_date'])); ?></td>
                            <td>
                                <?php if ($account['status'] === 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="show.php?id=<?php echo $account['id']; ?>" class="btn btn-sm btn-info">View</a>
                                <a href="edit.php?id=<?php echo $account['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                <button type="button" class="btn btn-sm btn-danger" onclick="showDeleteModal(<?php echo $account['id']; ?>, 'account', '<?php echo htmlspecialchars($account['full_name']); ?>')">Delete</button>
                                <form id="deleteForm_<?php echo $account['id']; ?>" method="POST" style="display:none;">
                                    <input type="hidden" name="delete_id" value="<?php echo $account['id']; ?>">
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

<script>
// Flight Statistics Chart
const ctx = document.getElementById('flightStatsChart');
if (ctx) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Completed', 'Confirmed'],
            datasets: [{
                label: 'Number of Flights',
                data: [<?php echo $completedCount; ?>, <?php echo $confirmedCount; ?>],
                backgroundColor: [
                    'rgba(37, 99, 235, 0.8)',
                    'rgba(22, 163, 74, 0.8)'
                ],
                borderColor: [
                    'rgba(37, 99, 235, 1)',
                    'rgba(22, 163, 74, 1)'
                ],
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            return 'Flights: ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 13,
                            weight: '600'
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}
</script>

<?php include "../templates/footer.php"; ?>

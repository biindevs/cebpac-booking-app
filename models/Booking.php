<?php
/**
 * Booking Model
 * Handles all database operations for bookings
 */

require_once __DIR__ . '/../config/db.php';

class Booking {
    private $conn;
    private $table = 'bookings';

    public $id;
    public $account_id;
    public $booking_reference;
    public $departure_city;
    public $arrival_city;
    public $departure_date;
    public $return_date;
    public $number_of_passengers;
    public $total_price;
    public $status;
    public $notes;
    public $itinerary_pdf;
    public $passengers_info;
    public $booking_date;
    public $last_updated;

    public function __construct() {
        global $db;
        $this->conn = $db;
    }

    /**
     * Automatically promote past trips to completed.
     * - One-way: departure date already passed
     * - Round-trip: return date already passed
     */
    public function refreshCompletedStatuses() {
        $query = "UPDATE {$this->table}
                  SET status = 'completed', last_updated = NOW()
                  WHERE status = 'confirmed'
                    AND (
                        (
                            (return_date IS NULL OR return_date = '')
                            AND departure_date < CURDATE()
                        )
                        OR
                        (
                            (return_date IS NOT NULL AND return_date <> '')
                            AND return_date < CURDATE()
                        )
                    )";
    
        if (!$this->conn->query($query)) {
            throw new Exception('Failed to refresh completed statuses: ' . $this->conn->error);
        }
    }
    

    /**
     * Get all bookings
     */
    public function getAll() {
        $query = "SELECT b.*, a.email, a.full_name 
                  FROM " . $this->table . " b
                  JOIN accounts a ON b.account_id = a.id
                  ORDER BY b.booking_date DESC";
        $result = $this->conn->query($query);

        if (!$result) {
            throw new Exception("Query failed: " . $this->conn->error);
        }

        return $result;
    }

    /**
     * Get booking by ID
     */
    public function getById($id) {
        $query = "SELECT b.*, a.email, a.full_name 
                  FROM " . $this->table . " b
                  JOIN accounts a ON b.account_id = a.id
                  WHERE b.id = ?";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /**
     * Get bookings by account ID
     * Sorted to prioritize upcoming departure dates (future dates first, then past dates)
     */
    public function getByAccountId($account_id) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE account_id = ?
                  ORDER BY 
                      (departure_date >= CURDATE()) DESC,
                      IF(departure_date >= CURDATE(), departure_date, '9999-12-31') ASC,
                      IF(departure_date < CURDATE(), departure_date, '0000-01-01') DESC";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $account_id);
        $stmt->execute();

        return $stmt->get_result();
    }

    /**
     * Create new booking
     */
    public function create($account_id, $booking_reference, $departure_city, $arrival_city, 
            $departure_date, $return_date, $number_of_passengers, $total_price, $status, $notes, $itinerary_pdf = null, $passengers_info = null) {

        // FIX: convert empty return dates to NULL
        $return_date = empty($return_date) ? NULL : $return_date;

        $query = "INSERT INTO " . $this->table . " 
        (account_id, booking_reference, departure_city, arrival_city, departure_date, 
        return_date, number_of_passengers, total_price, status, notes, itinerary_pdf, passengers_info) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
        throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param(
        "isssssidssss",
        $account_id,
        $booking_reference,
        $departure_city,
        $arrival_city,
        $departure_date,
        $return_date,
        $number_of_passengers,
        $total_price,
        $status,
        $notes,
        $itinerary_pdf,
        $passengers_info
        );

        if ($stmt->execute()) {
        return $this->conn->insert_id;
        } else {
        throw new Exception("Insert failed: " . $stmt->error);
        }
        }

    /**
     * Update booking
     */
            public function update($id, $departure_city, $arrival_city, $departure_date, $return_date, 
            $number_of_passengers, $total_price, $status, $notes, $itinerary_pdf = null, $passengers_info = null) {

        // FIX: convert empty return dates to NULL
        $return_date = empty($return_date) ? NULL : $return_date;

        $query = "UPDATE " . $this->table . " 
        SET departure_city = ?, arrival_city = ?, departure_date = ?, return_date = ?,
        number_of_passengers = ?, total_price = ?, status = ?, notes = ?, itinerary_pdf = ?, passengers_info = ?, last_updated = NOW()
        WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
        throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param(
        "ssssidssssi",
        $departure_city,
        $arrival_city,
        $departure_date,
        $return_date,
        $number_of_passengers,
        $total_price,
        $status,
        $notes,
        $itinerary_pdf,
        $passengers_info,
        $id
        );

        if ($stmt->execute()) {
        return $stmt->affected_rows > 0;
        } else {
        throw new Exception("Update failed: " . $stmt->error);
        }
        }


    /**
     * Delete booking
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        } else {
            throw new Exception("Delete failed: " . $stmt->error);
        }
    }

    /**
     * Get bookings by status
     */
    public function getByStatus($status) {
        $query = "SELECT b.*, a.email, a.full_name 
                  FROM " . $this->table . " b
                  JOIN accounts a ON b.account_id = a.id
                  WHERE b.status = ?
                  ORDER BY b.booking_date DESC";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("s", $status);
        $stmt->execute();

        return $stmt->get_result();
    }

    /**
     * Get upcoming bookings (next 90 days)
     */
    public function getUpcoming() {
        $query = "SELECT b.*, a.email, a.full_name,
                         DATEDIFF(b.departure_date, CURDATE()) as days_until_departure
                  FROM " . $this->table . " b
                  JOIN accounts a ON b.account_id = a.id
                  WHERE b.departure_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)
                    AND b.status = 'confirmed'
                  ORDER BY b.departure_date ASC";
        $result = $this->conn->query($query);

        if (!$result) {
            throw new Exception("Query failed: " . $this->conn->error);
        }

        return $result;
    }

    /**
     * Count total bookings
     */
    public function countAll() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        $result = $this->conn->query($query);
        $row = $result->fetch_assoc();

        return $row['total'];
    }

    /**
     * Count bookings by status
     */
    public function countByStatus($status) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE status = ?";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'];
    }

    /**
     * Search bookings
     */
    public function search($keyword) {
        $keyword = "%" . $this->conn->real_escape_string($keyword) . "%";
        $query = "SELECT b.*, a.email, a.full_name 
                  FROM " . $this->table . " b
                  JOIN accounts a ON b.account_id = a.id
                  WHERE b.booking_reference LIKE ? OR a.email LIKE ? OR a.full_name LIKE ?
                         OR b.departure_city LIKE ? OR b.arrival_city LIKE ?
                  ORDER BY b.booking_date DESC";
        
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("sssss", $keyword, $keyword, $keyword, $keyword, $keyword);
        $stmt->execute();

        return $stmt->get_result();
    }

    /**
     * Get flight statistics (completed and confirmed counts)
     */
    public function getFlightStatistics() {
        $query = "SELECT 
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_count
                  FROM " . $this->table;
        
        $result = $this->conn->query($query);

        if (!$result) {
            throw new Exception("Query failed: " . $this->conn->error);
        }

        return $result->fetch_assoc();
    }
}

?>

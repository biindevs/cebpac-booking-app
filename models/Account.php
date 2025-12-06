<?php
/**
 * Account Model
 * Handles all database operations for accounts
 */

require_once __DIR__ . '/../config/db.php';

class Account {
    private $conn;
    private $table = 'accounts';

    public $id;
    public $email;
    public $full_name;
    public $status;
    public $registration_date;
    public $last_updated;
    public $travel_funds;

    public function __construct() {
        global $db;
        $this->conn = $db;
    }

    /**
     * Get all accounts
     */
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY registration_date DESC";
        $result = $this->conn->query($query);

        if (!$result) {
            throw new Exception("Query failed: " . $this->conn->error);
        }

        return $result;
    }

    /**
     * Get account by ID
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
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
     * Get account by email
     */
    public function getByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /**
     * Create new account
     */
    public function create($email, $full_name, $status, $travel_funds = 0) {
        $query = "INSERT INTO " . $this->table . " 
                  (email, full_name, status, travel_funds) 
                  VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("sssd", $email, $full_name, $status, $travel_funds);

        if ($stmt->execute()) {
            return $this->conn->insert_id;
        } else {
            throw new Exception("Insert failed: " . $stmt->error);
        }
    }

    /**
     * Update account
     */
    public function update($id, $full_name, $status, $travel_funds = 0) {
        $query = "UPDATE " . $this->table . " 
                  SET full_name = ?, status = ?, travel_funds = ?, last_updated = NOW()
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("ssdi", $full_name, $status, $travel_funds, $id);

        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        } else {
            throw new Exception("Update failed: " . $stmt->error);
        }
    }

    /**
     * Delete account
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
     * Get account with booking summary
     */
    public function getWithBookingSummary($id) {
        $query = "SELECT 
                    a.id, a.email, a.full_name, a.status, a.registration_date, a.travel_funds,
                    COUNT(b.id) as total_bookings,
                    SUM(CASE WHEN b.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                    SUM(CASE WHEN b.status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
                    SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
                    SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                    SUM(b.total_price) as total_spent
                  FROM accounts a
                  LEFT JOIN bookings b ON a.id = b.account_id
                  WHERE a.id = ?
                  GROUP BY a.id";
        
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
     * Count total accounts
     */
    public function countAll() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        $result = $this->conn->query($query);
        $row = $result->fetch_assoc();

        return $row['total'];
    }

    /**
     * Search accounts
     */
    public function search($keyword) {
        $keyword = "%" . $this->conn->real_escape_string($keyword) . "%";
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE email LIKE ? OR full_name LIKE ?
                  ORDER BY registration_date DESC";
        
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("ss", $keyword, $keyword);
        $stmt->execute();

        return $stmt->get_result();
    }

    /**
     * Deduct travel funds
     */
    public function deductTravelFunds($id, $amount) {
        // First check if sufficient funds
        $account = $this->getById($id);
        if (!$account) {
            throw new Exception("Account not found");
        }

        if ($account['travel_funds'] < $amount) {
            throw new Exception("Insufficient travel funds. Required: ₱" . number_format($amount, 2) . ", Available: ₱" . number_format($account['travel_funds'], 2));
        }

        $query = "UPDATE " . $this->table . " 
                  SET travel_funds = travel_funds - ?, last_updated = NOW()
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("di", $amount, $id);

        if ($stmt->execute()) {
            return true;
        } else {
            throw new Exception("Deduct failed: " . $stmt->error);
        }
    }

    /**
     * Add travel funds (refund)
     */
    public function addTravelFunds($id, $amount) {
        $query = "UPDATE " . $this->table . " 
                  SET travel_funds = travel_funds + ?, last_updated = NOW()
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("di", $amount, $id);

        if ($stmt->execute()) {
            return true;
        } else {
            throw new Exception("Add failed: " . $stmt->error);
        }
    }
}

?>

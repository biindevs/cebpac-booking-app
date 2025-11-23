<?php
/**
 * Account Controller
 * Handles all account-related operations
 */

require_once __DIR__ . '/../models/Account.php';

class AccountController {
    private $account;

    public function __construct() {
        $this->account = new Account();
    }

    /**
     * Display all accounts
     */
    public function index() {
        try {
            $result = $this->account->getAll();
            $accounts = [];
            
            while ($row = $result->fetch_assoc()) {
                $accounts[] = $row;
            }
            
            return $accounts;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Display single account with bookings
     */
    public function show($id) {
        try {
            $account = $this->account->getWithBookingSummary($id);
            
            if (!$account) {
                return ['error' => 'Account not found'];
            }
            
            return $account;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Create new account
     */
    public function create($email, $full_name, $status, $travel_funds = 0) {
        try {
            // Validate input
            if (empty($email) || empty($full_name) || empty($status)) {
                return ['error' => 'Email, full name, and status are required'];
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['error' => 'Invalid email format'];
            }

            if ($travel_funds < 0) {
                return ['error' => 'Travel funds cannot be negative'];
            }

            // Check if email already exists
            $existing = $this->account->getByEmail($email);
            if ($existing) {
                return ['error' => 'Email already registered'];
            }

            $id = $this->account->create($email, $full_name, $status, $travel_funds);
            
            return [
                'success' => true,
                'message' => 'Account created successfully',
                'id' => $id
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Update account
     */
    public function update($id, $full_name, $status, $travel_funds = 0) {
        try {
            // Validate input
            if (empty($full_name) || empty($status)) {
                return ['error' => 'Full name and status are required'];
            }

            if ($travel_funds < 0) {
                return ['error' => 'Travel funds cannot be negative'];
            }

            // Check if account exists
            $account = $this->account->getById($id);
            if (!$account) {
                return ['error' => 'Account not found'];
            }

            $updated = $this->account->update($id, $full_name, $status, $travel_funds);
            
            return [
                'success' => true,
                'message' => 'Account updated successfully'
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Delete account
     */
    public function delete($id) {
        try {
            // Check if account exists
            $account = $this->account->getById($id);
            if (!$account) {
                return ['error' => 'Account not found'];
            }

            $deleted = $this->account->delete($id);
            
            if ($deleted) {
                return [
                    'success' => true,
                    'message' => 'Account deleted successfully'
                ];
            } else {
                return ['error' => 'Failed to delete account'];
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Search accounts
     */
    public function search($keyword) {
        try {
            if (empty($keyword)) {
                return ['error' => 'Search keyword required'];
            }

            $result = $this->account->search($keyword);
            $accounts = [];
            
            while ($row = $result->fetch_assoc()) {
                $accounts[] = $row;
            }
            
            return $accounts;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}

?>

<?php
/**
 * Booking Controller
 * Handles all booking-related operations
 */

require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Account.php';

class BookingController {
    private $booking;
    private $account;

    public function __construct() {
        $this->booking = new Booking();
        $this->account = new Account();

        try {
            $this->booking->refreshCompletedStatuses();
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    /**
     * Display all bookings
     */
    public function index() {
        try {
            $result = $this->booking->getAll();
            $bookings = [];
            
            while ($row = $result->fetch_assoc()) {
                $bookings[] = $row;
            }
            
            return $bookings;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Display single booking
     */
    public function show($id) {
        try {
            $booking = $this->booking->getById($id);
            
            if (!$booking) {
                return ['error' => 'Booking not found'];
            }
            
            return $booking;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get bookings for account
     */
    public function getByAccount($account_id) {
        try {
            $result = $this->booking->getByAccountId($account_id);
            $bookings = [];
            
            while ($row = $result->fetch_assoc()) {
                $bookings[] = $row;
            }
            
            return $bookings;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Create new booking
     */
    public function create($account_id, $booking_reference, $departure_city, $arrival_city,
                          $departure_date, $return_date, $number_of_passengers, $total_price, $status, $notes, $itinerary_pdf = null, $passengers_info = null) {
        try {
            // Validate required fields
            if (empty($account_id) || empty($booking_reference) || empty($departure_city) || 
                empty($arrival_city) || empty($departure_date) || empty($number_of_passengers) || 
                empty($total_price) || empty($status)) {
                return ['error' => 'All required fields must be filled'];
            }

            // Validate account exists
            $account = $this->account->getById($account_id);
            if (!$account) {
                return ['error' => 'Account not found'];
            }

            // Validate dates
            if (strtotime($departure_date) < time()) {
                return ['error' => 'Departure date cannot be in the past'];
            }

            if (!empty($return_date) && strtotime($return_date) <= strtotime($departure_date)) {
                return ['error' => 'Return date must be after departure date'];
            }

            // Validate numeric values
            if ($number_of_passengers < 1) {
                return ['error' => 'Number of passengers must be at least 1'];
            }

            if ($total_price <= 0) {
                return ['error' => 'Price must be greater than 0'];
            }

            $paidStatuses = ['confirmed', 'completed'];

            // If status is paid, validate and deduct travel funds
            if (in_array($status, $paidStatuses)) {
                if ($account['travel_funds'] < $total_price) {
                    return ['error' => 'Insufficient travel funds. Required: ₱' . number_format($total_price, 2) . ', Available: ₱' . number_format($account['travel_funds'], 2)];
                }
                // Deduct travel funds
                $this->account->deductTravelFunds($account_id, $total_price);
            }

            $id = $this->booking->create($account_id, $booking_reference, $departure_city, $arrival_city,
                                        $departure_date, $return_date, $number_of_passengers, $total_price, $status, $notes, $itinerary_pdf, $passengers_info);
            
            return [
                'success' => true,
                'message' => 'Booking created successfully',
                'id' => $id
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Update booking
     */
    public function update($id, $departure_city, $arrival_city, $departure_date, $return_date,
                          $number_of_passengers, $total_price, $status, $notes, $itinerary_pdf = null, $passengers_info = null) {
        try {
            // Validate required fields
            if (empty($departure_city) || empty($arrival_city) || empty($departure_date) ||
                empty($number_of_passengers) || empty($total_price) || empty($status)) {
                return ['error' => 'All required fields must be filled'];
            }

            // Check if booking exists
            $booking = $this->booking->getById($id);
            if (!$booking) {
                return ['error' => 'Booking not found'];
            }

            // Get account info for travel funds management
            $account = $this->account->getById($booking['account_id']);
            $old_status = $booking['status'];
            $old_price = floatval($booking['total_price']); // Convert to float for accurate comparison
            $new_price = floatval($total_price); // Convert to float for accurate comparison
            $paidStatuses = ['confirmed', 'completed'];
            $oldWasPaid = in_array($old_status, $paidStatuses);
            $newIsPaid = in_array($status, $paidStatuses);

            // Validate dates
            if (strtotime($departure_date) < time()) {
                return ['error' => 'Departure date cannot be in the past'];
            }

            if (!empty($return_date) && strtotime($return_date) <= strtotime($departure_date)) {
                return ['error' => 'Return date must be after departure date'];
            }

            // Validate numeric values
            if ($number_of_passengers < 1) {
                return ['error' => 'Number of passengers must be at least 1'];
            }

            if ($new_price <= 0) {
                return ['error' => 'Price must be greater than 0'];
            }

            // Check if status or price actually changed (using small tolerance for floating point comparison)
            $statusChanged = ($old_status !== $status);
            $priceChanged = abs($old_price - $new_price) > 0.01; // Use 0.01 tolerance for floating point comparison

            // Only adjust travel funds if there's an actual change in status or price
            if ($statusChanged || $priceChanged) {
            if (!$oldWasPaid && $newIsPaid) {
                // pending/cancelled → confirmed: deduct new amount
                    if ($account['travel_funds'] < $new_price) {
                        return ['error' => 'Insufficient travel funds. Required: ₱' . number_format($new_price, 2) . ', Available: ₱' . number_format($account['travel_funds'], 2)];
                }
                    $this->account->deductTravelFunds($booking['account_id'], $new_price);
            } elseif ($oldWasPaid && !$newIsPaid) {
                // confirmed → pending/cancelled: refund old amount
                $this->account->addTravelFunds($booking['account_id'], $old_price);
                } elseif ($oldWasPaid && $newIsPaid && $priceChanged) {
                // confirmed → confirmed with price change: adjust difference
                    $difference = $new_price - $old_price;
                    if ($difference > 0.01) {
                    // Price increased: deduct difference
                    if ($account['travel_funds'] < $difference) {
                        return ['error' => 'Insufficient travel funds for price increase. Required: ₱' . number_format($difference, 2) . ', Available: ₱' . number_format($account['travel_funds'], 2)];
                    }
                    $this->account->deductTravelFunds($booking['account_id'], $difference);
                    } elseif ($difference < -0.01) {
                    // Price decreased: refund difference
                    $this->account->addTravelFunds($booking['account_id'], abs($difference));
                    }
                }
            }
            // If status and price haven't changed, skip travel funds adjustment entirely

            $updated = $this->booking->update($id, $departure_city, $arrival_city, $departure_date,
                                             $return_date, $number_of_passengers, $total_price, $status, $notes, $itinerary_pdf, $passengers_info);
            
            return [
                'success' => true,
                'message' => 'Booking updated successfully'
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Delete booking
     */
    public function delete($id) {
        try {
            // Check if booking exists
            $booking = $this->booking->getById($id);
            if (!$booking) {
                return ['error' => 'Booking not found'];
            }

            // If booking was confirmed, refund the travel funds
            if ($booking['status'] === 'confirmed') {
                $this->account->addTravelFunds($booking['account_id'], $booking['total_price']);
            }

            $deleted = $this->booking->delete($id);
            
            if ($deleted) {
                return [
                    'success' => true,
                    'message' => 'Booking deleted successfully'
                ];
            } else {
                return ['error' => 'Failed to delete booking'];
            }
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get bookings by status
     */
    public function getByStatus($status) {
        try {
            $valid_statuses = ['confirmed', 'pending', 'cancelled', 'completed'];
            if (!in_array($status, $valid_statuses)) {
                return ['error' => 'Invalid status'];
            }

            $result = $this->booking->getByStatus($status);
            $bookings = [];
            
            while ($row = $result->fetch_assoc()) {
                $bookings[] = $row;
            }
            
            return $bookings;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get upcoming bookings
     */
    public function getUpcoming() {
        try {
            $result = $this->booking->getUpcoming();
            $bookings = [];
            
            while ($row = $result->fetch_assoc()) {
                $bookings[] = $row;
            }
            
            return $bookings;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Search bookings
     */
    public function search($keyword) {
        try {
            if (empty($keyword)) {
                return ['error' => 'Search keyword required'];
            }

            $result = $this->booking->search($keyword);
            $bookings = [];
            
            while ($row = $result->fetch_assoc()) {
                $bookings[] = $row;
            }
            
            return $bookings;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}

?>

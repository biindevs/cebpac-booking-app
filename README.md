# CebPac Booking App
Flight booking system for Cebu Pacific Airlines

## Quick Start Locally

```bash
# 1. Start XAMPP (Apache + MySQL)
# 2. Create database
mysql -u root < cebpac_booking_db.sql

# 3. Access app
http://localhost/cebpac-booking-app
```

## Deployment

See [RENDER_DEPLOYMENT.md](RENDER_DEPLOYMENT.md) for Render.com free deployment guide.

## Features

- ✅ User account management (email, name, travel funds)
- ✅ Flight booking with automatic fund deduction
- ✅ Passenger details collection (first name, last name, title)
- ✅ PDF itinerary upload & storage
- ✅ Booking management (create, edit, view, delete)
- ✅ Minimalist, clean UI design
- ✅ CebPac yellow branding

## Tech Stack

- **Backend:** PHP 8.0.25 with MySQL
- **Frontend:** Bootstrap 5.3.0 + Custom CSS
- **Database:** MySQL with prepared statements
- **File Storage:** Server uploads directory

## File Structure

```
cebpac-booking-app/
├── config/
│   └── database.php        # Database configuration
├── models/
│   ├── Account.php         # Account model
│   └── Booking.php         # Booking model
├── controllers/
│   ├── AccountController.php
│   └── BookingController.php
├── views/
│   ├── accounts/           # Account views
│   ├── bookings/           # Booking views
│   ├── layouts/            # Layout templates
│   └── index.php           # Dashboard
├── public/
│   ├── css/
│   │   └── style.css       # Minimalist design
│   ├── js/
│   │   └── scripts.js      # Form interactions
│   └── uploads/            # PDF files
├── cebpac_booking_db.sql   # Database schema
└── RENDER_DEPLOYMENT.md    # Deployment guide
```

## Database Schema

### accounts
- id, email, full_name, status, registration_date, last_updated, travel_funds

### bookings
- id, account_id, booking_reference, departure_city, arrival_city
- departure_date, return_date, number_of_passengers, total_price, status
- notes, booking_date, last_updated, itinerary_pdf, passengers_info (JSON)

## API Endpoints

### Accounts
- `POST /index.php?action=create_account` - Create new account
- `GET /index.php?action=show_account&id=ID` - View account
- `POST /index.php?action=edit_account&id=ID` - Edit account
- `GET /index.php?action=delete_account&id=ID` - Delete account

### Bookings
- `POST /index.php?action=create_booking` - Create booking
- `GET /index.php?action=view_booking&id=ID` - View booking
- `POST /index.php?action=edit_booking&id=ID` - Edit booking
- `GET /index.php?action=delete_booking&id=ID` - Delete booking

## License

Open source - use freely

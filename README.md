# Restaurant Reservation & Management System

A web-based restaurant management platform built with PHP, featuring online reservation booking, admin dashboard, and menu management.

## Features

- **Online Reservations**: Customers can book tables directly through the website
- **Admin Dashboard**: Manage reservations, menu items, and restaurant operations
- **User Authentication**: Secure login system for customers and administrators
- **Menu Management**: Add and manage restaurant menu items
- **Contact Form**: Easy customer communication
- **Responsive Design**: Works across desktop and mobile devices

## Tech Stack

- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript
- **Server**: Apache (XAMPP)

## Installation

### Prerequisites
- XAMPP (or similar PHP/Apache/MySQL setup)
- PHP 7.0 or higher
- MySQL 5.7 or higher

### Setup Steps

1. **Clone or extract the project** to your XAMPP htdocs folder:
   ```
   c:\xampp\htdocs\restaurant\
   ```

2. **Create the database**:
   - Open phpMyAdmin (typically at `http://localhost/phpmyadmin`)
   - Import the database file (if available) or create tables manually
   - Update database credentials in `db.php`

3. **Configure database connection**:
   - Edit `db.php` with your MySQL credentials:
   ```php
   $host = 'localhost';
   $username = 'root';
   $password = '';
   $database = 'restaurant';
   ```

4. **Start services**:
   - Start Apache and MySQL from XAMPP Control Panel
   - Navigate to `http://localhost/restaurant/`

## Project Structure

```
restaurant/
├── index.php              # Homepage
├── about.php              # About page
├── contact.php            # Contact form
├── reservations.php       # Reservation booking page
├── login.php              # User login
├── logout.php             # User logout
├── admin_dashboard.php    # Admin panel
├── admin_reservation.php  # Manage reservations (admin)
├── add_item.php           # Add menu items (admin)
├── db.php                 # Database connection
├── uploads/               # Uploaded files (menu images, etc.)
└── README.md              # This file
```

## Usage

### For Customers
1. Browse the website and view restaurant information
2. Click on **Reservations** to book a table
3. Fill in your details and preferred date/time
4. Receive confirmation of your reservation

### For Administrators
1. Log in with admin credentials
2. Access **Admin Dashboard** to:
   - View and manage all reservations
   - Add new menu items
   - Update restaurant information

## File Descriptions

- **db.php**: Database connection configuration
- **index.php**: Main landing page
- **reservations.php**: Reservation booking interface
- **admin_dashboard.php**: Administrative control panel
- **add_item.php**: Menu item management
- **login.php**: Authentication page
- **contact.php**: Customer contact form

## Security Considerations

- Always use prepared statements for database queries
- Validate and sanitize all user inputs
- Use HTTPS in production
- Keep sensitive credentials out of version control
- Implement proper session management

## Future Enhancements

- Email confirmations for reservations
- Payment integration
- Ratings and reviews system
- Table availability calendar
- Multi-language support
- Mobile app version

## License

This project is provided as-is for educational and commercial use.

## Support

For issues or questions, please create an issue in the repository or contact the development team.
sunset00x
---

**Last Updated**: May 2026

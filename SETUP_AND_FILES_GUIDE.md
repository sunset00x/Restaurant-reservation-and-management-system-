# Restaurant System - Complete Setup & Files Guide

## Table of Contents
1. [Initial Setup Steps](#initial-setup-steps)
2. [Database Setup](#database-setup)
3. [File Descriptions](#file-descriptions)
4. [Step-by-Step Implementation](#step-by-step-implementation)
5. [Configuration](#configuration)
6. [Testing Checklist](#testing-checklist)

---

## Initial Setup Steps

### Step 1: Create Project Folder
```
1. Navigate to: C:\xampp\htdocs\
2. Create new folder: restaurant
3. Extract all project files into this folder
```

### Step 2: Start XAMPP Services
```
1. Open XAMPP Control Panel
2. Click "Start" for Apache
3. Click "Start" for MySQL
4. Verify both show green indicators
```

### Step 3: Create Database
```
1. Open browser and go to: http://localhost/phpmyadmin
2. Click "New" to create new database
3. Database name: restaurant
4. Click "Create"
```

### Step 4: Create Database Tables
Execute these SQL queries in phpMyAdmin:

```sql
-- Users table for login
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  is_admin BOOLEAN DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Reservations table
CREATE TABLE reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  customer_name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  reservation_date DATE NOT NULL,
  reservation_time TIME NOT NULL,
  number_of_guests INT NOT NULL,
  special_requests TEXT,
  status VARCHAR(20) DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Menu items table
CREATE TABLE menu_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  price DECIMAL(10, 2) NOT NULL,
  category VARCHAR(50),
  image_path VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Contact messages table
CREATE TABLE contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  subject VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## Database Setup

### Database Name
```
restaurant
```

### Tables Overview

#### 1. **users** - User authentication
- `id`: Primary key
- `username`: Unique username for login
- `email`: User email address
- `password`: Hashed password
- `is_admin`: Flag for admin privileges (0 = customer, 1 = admin)
- `created_at`: Account creation timestamp

#### 2. **reservations** - Table reservations
- `id`: Primary key
- `user_id`: Reference to users table
- `customer_name`: Guest name
- `email`: Guest email
- `phone`: Contact phone
- `reservation_date`: Booking date
- `reservation_time`: Booking time
- `number_of_guests`: Party size
- `special_requests`: Special notes
- `status`: Pending, Confirmed, Cancelled
- `created_at`: Booking timestamp

#### 3. **menu_items** - Restaurant menu
- `id`: Primary key
- `name`: Dish name
- `description`: Dish description
- `price`: Item price
- `category`: Food category (Appetizer, Main, Dessert, Beverage)
- `image_path`: Path to dish image
- `created_at`: Item creation timestamp

#### 4. **contact_messages** - Contact form submissions
- `id`: Primary key
- `name`: Visitor name
- `email`: Visitor email
- `subject`: Message subject
- `message`: Message content
- `created_at`: Submission timestamp

---

## File Descriptions

### 📄 **db.php** - Database Connection
**Purpose**: Central database connection configuration

```php
<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'restaurant';

try {
    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
```

**Key Points**:
- Included in every file that needs database access
- Change credentials here if different from defaults
- Use `$conn` variable to query database

---

### 📄 **index.php** - Homepage
**Purpose**: Main landing page for the restaurant

**Content Includes**:
- Navigation menu
- Restaurant welcome banner
- Featured menu section
- Call-to-action buttons
- Quick links to reservations and contact

**Key Sections**:
```
1. Header/Navigation
2. Hero section with restaurant info
3. Featured dishes display
4. Testimonials section
5. Footer with contact info
```

---

### 📄 **about.php** - About Page
**Purpose**: Restaurant information and history

**Content**:
- Restaurant story/history
- Team information
- Mission and values
- Operating hours
- Location and contact details

---

### 📄 **contact.php** - Contact Form
**Purpose**: Customer communication page

**Features**:
- Contact form with fields:
  - Name
  - Email
  - Subject
  - Message
- Form submission to contact_messages table
- Success/error messages
- Displays restaurant contact info

**Form Processing**:
```
1. Validate input data
2. Sanitize inputs
3. Insert into contact_messages table
4. Show confirmation message
5. Optional: Send email notification
```

---

### 📄 **reservations.php** - Reservation Booking
**Purpose**: Online table reservation system

**Features**:
- Reservation form with fields:
  - Customer name
  - Email address
  - Phone number
  - Reservation date (date picker)
  - Reservation time (time picker)
  - Number of guests
  - Special requests (textarea)
- Form validation
- Database insertion
- Confirmation message

**Workflow**:
```
1. User fills reservation form
2. Validate all fields are complete
3. Check date/time availability
4. Insert into reservations table
5. Show confirmation with reservation ID
6. Optional: Send confirmation email
```

---

### 📄 **login.php** - User Authentication
**Purpose**: Customer and admin login page

**Features**:
- Login form with:
  - Username/Email
  - Password
- Session creation
- User type detection (admin or customer)
- Redirect to appropriate dashboard
- Error messages for failed login

**Security**:
- Password hashing (use `password_hash()` and `password_verify()`)
- Session management
- CSRF token validation
- Input validation

---

### 📄 **logout.php** - User Logout
**Purpose**: Session termination

**Actions**:
```php
1. Destroy session
2. Clear session variables
3. Redirect to homepage or login
```

---

### 📄 **admin_dashboard.php** - Admin Control Panel
**Purpose**: Administrative management interface

**Sections**:
1. **Dashboard Overview**
   - Total reservations
   - Pending bookings
   - Total customers

2. **Reservation Management**
   - View all reservations
   - Filter by date/status
   - Approve/reject reservations
   - Cancel reservations
   - Edit reservation details

3. **Menu Management**
   - View all menu items
   - Link to add new items
   - Edit existing items
   - Delete items

4. **User Management**
   - View registered users
   - Manage user roles

**Access Control**:
```php
// Check if user is admin
if (!isset($_SESSION['user']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}
```

---

### 📄 **admin_reservation.php** - Manage Reservations
**Purpose**: Detailed reservation management interface

**Features**:
- List all reservations with status
- Search and filter options:
  - By date range
  - By status (Pending, Confirmed, Cancelled)
  - By customer name
- Action buttons:
  - View details
  - Approve/Confirm
  - Reject/Cancel
  - Edit
  - Delete
- Bulk actions (mark multiple as confirmed, etc.)

**Database Operations**:
```sql
-- View all reservations
SELECT * FROM reservations ORDER BY reservation_date DESC;

-- Update status
UPDATE reservations SET status = 'Confirmed' WHERE id = ?;

-- Delete reservation
DELETE FROM reservations WHERE id = ?;
```

---

### 📄 **add_item.php** - Add Menu Items
**Purpose**: Admin interface to add/edit menu items

**Features**:
- Form to add new menu item:
  - Item name
  - Description
  - Price
  - Category (dropdown)
  - Image upload
- Edit existing items
- Delete items
- Form validation

**Form Fields**:
```
1. Name: Text input (required)
2. Description: Textarea (required)
3. Price: Number input (required)
4. Category: Dropdown (Appetizer, Main, Dessert, Beverage)
5. Image: File upload (jpg, png)
```

**File Upload**:
```php
// Validate image
$allowed = ['jpg', 'jpeg', 'png', 'gif'];
$filename = $_FILES['image']['name'];
$ext = pathinfo($filename, PATHINFO_EXTENSION);
if (in_array($ext, $allowed)) {
    $newname = uniqid() . '.' . $ext;
    move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $newname);
}
```

---

### 📁 **uploads/** - File Storage
**Purpose**: Store uploaded files (menu images, etc.)

**Contents**:
- Menu item images
- Temporary files
- User-uploaded content

**File Organization**:
```
uploads/
├── menu_images/
├── profile_pics/
└── documents/
```

---

## Step-by-Step Implementation

### Phase 1: Database & Core Files (Week 1)
```
1. Create XAMPP folder structure ✓
2. Create restaurant database ✓
3. Create tables with SQL ✓
4. Create db.php ✓
5. Test database connection
```

### Phase 2: Frontend Pages (Week 2)
```
1. Create index.php ✓
2. Create about.php ✓
3. Create contact.php with form ✓
4. Create HTML structure and CSS
5. Add responsive design
```

### Phase 3: Authentication (Week 3)
```
1. Create login.php ✓
2. Create logout.php ✓
3. Implement session management
4. Create user registration (optional)
5. Add password hashing
```

### Phase 4: Reservation System (Week 4)
```
1. Create reservations.php ✓
2. Build reservation form
3. Add form validation
4. Insert data to database
5. Add confirmation emails (optional)
```

### Phase 5: Admin Panel (Week 5)
```
1. Create admin_dashboard.php ✓
2. Create admin_reservation.php ✓
3. Create add_item.php ✓
4. Build admin interface
5. Add management features
```

---

## Configuration

### db.php Configuration
```php
// Default XAMPP settings
$host = 'localhost';        // Server location
$username = 'root';         // Default XAMPP user
$password = '';             // Default (empty)
$database = 'restaurant';   // Your database name
```

### Session Configuration (add to top of pages)
```php
<?php
session_start();
include 'db.php';

// Set session timeout (30 minutes)
$timeout = 1800;
if (isset($_SESSION['last_activity']) && 
    (time() - $_SESSION['last_activity']) > $timeout) {
    session_destroy();
    header("Location: login.php");
}
$_SESSION['last_activity'] = time();
?>
```

### Email Configuration (optional for notifications)
```php
$to = $_POST['email'];
$subject = "Reservation Confirmation";
$message = "Your reservation has been confirmed!";
mail($to, $subject, $message);
```

---

## Testing Checklist

### Database Testing
- [ ] Can connect to database from db.php
- [ ] All tables created successfully
- [ ] Can insert test data into each table
- [ ] Can retrieve data from tables

### Homepage Testing
- [ ] index.php loads without errors
- [ ] All links work correctly
- [ ] Navigation menu functional
- [ ] Images/CSS load properly

### Contact Form Testing
- [ ] Form submission works
- [ ] Data saved to contact_messages table
- [ ] Success message displays
- [ ] Error handling works

### Reservation Testing
- [ ] Form displays correctly
- [ ] All fields accept input
- [ ] Form validation works
- [ ] Data saves to reservations table
- [ ] Confirmation message shows

### Login Testing
- [ ] Login page loads
- [ ] Valid credentials work
- [ ] Invalid credentials show error
- [ ] Admin vs customer redirect works
- [ ] Session created successfully

### Admin Panel Testing
- [ ] Only admins can access
- [ ] Can view all reservations
- [ ] Can update reservation status
- [ ] Can add menu items
- [ ] File uploads work
- [ ] Can delete items/reservations

### Security Testing
- [ ] SQL injection prevention
- [ ] Input validation working
- [ ] Session security
- [ ] Unauthorized access blocked
- [ ] Password properly hashed

---

## Troubleshooting

### Common Issues & Solutions

**Problem**: "Connection failed: Unknown database"
- **Solution**: Check database name in db.php matches created database

**Problem**: "Session not working"
- **Solution**: Ensure session_start() is first line in PHP files

**Problem**: "File upload not working"
- **Solution**: Check uploads/ folder exists and has write permissions

**Problem**: "Login not redirecting"
- **Solution**: Check admin flag in users table (is_admin = 1 for admin)

**Problem**: "Form not submitting"
- **Solution**: Verify form method is POST and action points to correct file

---

## Project Structure Summary

```
restaurant/
│
├── index.php                    # Homepage
├── about.php                    # About page
├── contact.php                  # Contact form
├── reservations.php             # Reservation booking
├── login.php                    # User login
├── logout.php                   # User logout
│
├── admin_dashboard.php          # Admin home panel
├── admin_reservation.php        # Manage reservations
├── add_item.php                 # Add/edit menu items
│
├── db.php                       # Database connection
│
├── uploads/                     # Uploaded files folder
│   └── (menu images, etc.)
│
├── README.md                    # Project overview
└── SETUP_AND_FILES_GUIDE.md    # This file
```

---

## Next Steps

1. **Customize Styling**: Add CSS for professional look
2. **Add Error Logging**: Track errors for debugging
3. **Email Integration**: Send confirmation emails
4. **Payment Integration**: Add online payment option
5. **Mobile Optimization**: Ensure mobile responsiveness
6. **Security Hardening**: Use prepared statements everywhere
7. **User Profile Page**: Let users manage their account
8. **Review System**: Add ratings and reviews

---

**Document Version**: 1.0  
**Last Updated**: May 8, 2026  
**Status**: Complete Setup Guide

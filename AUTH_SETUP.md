# Authentication System Setup Guide

## Overview
Complete registration and login system with real-time validation, password strength indicator, and modern UI design.

## Files Created

### PHP Classes
- **`classes/User.php`** - User authentication class with OOP methods
  - `register()` - Register new user with password hashing
  - `login()` - Authenticate user with password verification
  - `emailExists()` - Check if email is already registered
  - `usernameExists()` - Check if username is taken
  - `validateEmail()` - Email format validation
  - `validatePassword()` - Password strength validation
  - `validateUsername()` - Username format validation

### Pages
- **`register.php`** - User registration page with validation
- **`login.php`** - User login page
- **`logout.php`** - Session destruction and logout

### Assets
- **`assets/css/auth.css`** - Authentication pages styling
- **`assets/js/auth-validation.js`** - Real-time form validation

### Database
- **`database/setup.sql`** - Enhanced users table schema

## Database Schema

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100),
    postal_code VARCHAR(20),
    is_active TINYINT(1) DEFAULT 1,
    email_verified TINYINT(1) DEFAULT 0,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
);
```

## Installation Steps

### 1. Database Setup
```bash
# Open phpMyAdmin (http://localhost/phpmyadmin)
# Import or run the SQL file: database/setup.sql
```

Or run manually:
```sql
DROP DATABASE IF EXISTS electronics_store;
CREATE DATABASE electronics_store;
USE electronics_store;
-- Then run the rest of setup.sql
```

### 2. Test the System

#### Register a New User
1. Navigate to: `http://localhost/compi/register.php`
2. Fill in the registration form:
   - Full Name: John Doe
   - Username: johndoe
   - Email: john@example.com
   - Phone: +1234567890
   - Password: Test@123
   - Confirm Password: Test@123
3. Check "I agree to terms"
4. Click "Create Account"

#### Login
1. Navigate to: `http://localhost/compi/login.php`
2. Enter credentials:
   - Email/Username: john@example.com (or johndoe)
   - Password: Test@123
3. Click "Login"

## Validation Features

### Real-Time Validation (On Focus/Blur)

#### Full Name
- **Required**: Yes
- **Min Length**: 2 characters
- **Pattern**: Letters and spaces only
- **Message**: "Full name must contain only letters and spaces"

#### Username
- **Required**: Yes
- **Length**: 3-50 characters
- **Pattern**: Alphanumeric and underscore only
- **Check**: Unique in database
- **Message**: "Username must be 3-50 characters (letters, numbers, underscore only)"

#### Email
- **Required**: Yes
- **Pattern**: Valid email format
- **Check**: Unique in database
- **Message**: "Please enter a valid email address"

#### Phone
- **Required**: No
- **Pattern**: Numbers, spaces, dashes, plus, parentheses
- **Min Length**: 10 characters
- **Message**: "Please enter a valid phone number"

#### Password
- **Required**: Yes
- **Min Length**: 8 characters
- **Pattern**: Must contain:
  - At least 1 uppercase letter
  - At least 1 lowercase letter
  - At least 1 number
- **Strength Indicator**: Visual bar showing Weak/Medium/Strong
- **Message**: "Password must be 8+ characters with uppercase, lowercase, and number"

#### Confirm Password
- **Required**: Yes
- **Match**: Must match password field
- **Message**: "Passwords do not match"

### Visual Feedback

#### Input States
- **Default**: Gray border
- **Focused**: Blue border with glow effect
- **Error**: Red border with error message
- **Success**: Green border with checkmark

#### Password Strength Indicator
- **Weak** (0-49%): Red bar
- **Medium** (50-74%): Orange bar
- **Strong** (75-100%): Green bar

## Security Features

### Password Security
- **Hashing**: BCrypt algorithm (`PASSWORD_BCRYPT`)
- **Salt**: Automatic salt generation
- **Cost**: Default cost factor (10)

### SQL Injection Prevention
- **PDO Prepared Statements**: All queries use parameterized statements
- **Input Sanitization**: `htmlspecialchars()` and `strip_tags()`

### XSS Prevention
- **Output Escaping**: All user input escaped before display
- **HTML Entities**: Special characters converted

### Session Security
- **Session Management**: PHP sessions for authentication
- **Remember Me**: Secure token-based (30 days)
- **Last Login**: Timestamp tracking

## User Class Methods

### Authentication Methods

```php
// Register new user
$user->username = "johndoe";
$user->email = "john@example.com";
$user->password = "Test@123";
$user->full_name = "John Doe";
$user->phone = "+1234567890";
$result = $user->register();

// Login user
$user->email = "john@example.com";
$user->password = "Test@123";
$result = $user->login();
// Returns: true (success), false (failed), "inactive" (account disabled)

// Check if email exists
$user->email = "john@example.com";
$exists = $user->emailExists(); // Returns boolean

// Check if username exists
$user->username = "johndoe";
$exists = $user->usernameExists(); // Returns boolean
```

### Validation Methods (Static)

```php
// Validate email format
$valid = User::validateEmail("john@example.com"); // Returns boolean

// Validate password strength
$valid = User::validatePassword("Test@123"); // Returns boolean

// Validate username format
$valid = User::validateUsername("johndoe"); // Returns boolean
```

## Session Variables

After successful login, the following session variables are set:

```php
$_SESSION['user_id']    // User ID
$_SESSION['username']   // Username
$_SESSION['email']      // Email address
$_SESSION['full_name']  // Full name
```

### Check if User is Logged In

```php
session_start();
if (isset($_SESSION['user_id'])) {
    // User is logged in
    echo "Welcome, " . $_SESSION['full_name'];
} else {
    // User is not logged in
    header("Location: login.php");
    exit();
}
```

## JavaScript Validation Events

### Event Listeners

1. **Focus**: Shows input hints, adds focused styling
2. **Blur**: Validates field when user leaves input
3. **Input**: Real-time validation as user types (after first blur)
4. **Submit**: Validates all fields before form submission

### Password Features

```javascript
// Toggle password visibility
document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function() {
        // Toggles between password and text input type
    });
});

// Password strength calculation
function updatePasswordStrength(password) {
    // Calculates strength based on:
    // - Length (8+ chars)
    // - Lowercase letters
    // - Uppercase letters
    // - Numbers
}
```

## Customization

### Change Validation Rules

Edit `assets/js/auth-validation.js`:

```javascript
const validationRules = {
    username: {
        required: true,
        minLength: 3,  // Change minimum length
        maxLength: 50, // Change maximum length
        pattern: /^[a-zA-Z0-9_]+$/, // Modify pattern
        message: 'Your custom message'
    }
};
```

### Change Password Requirements

Edit `classes/User.php`:

```php
public static function validatePassword($password) {
    // Modify requirements here
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number = preg_match('@[0-9]@', $password);
    $special = preg_match('@[^\w]@', $password); // Add special char
    $length = strlen($password) >= 10; // Change min length
    
    return $uppercase && $lowercase && $number && $special && $length;
}
```

### Styling Customization

Edit `assets/css/auth.css`:

```css
:root {
    --primary-color: #6366f1;  /* Change primary color */
    --success-color: #10b981;  /* Change success color */
    --error-color: #ef4444;    /* Change error color */
}
```

## Testing Checklist

- [ ] Register with valid data - Should succeed
- [ ] Register with existing email - Should show error
- [ ] Register with existing username - Should show error
- [ ] Register with weak password - Should show error
- [ ] Register with mismatched passwords - Should show error
- [ ] Login with correct credentials - Should succeed
- [ ] Login with wrong password - Should show error
- [ ] Login with non-existent user - Should show error
- [ ] Password visibility toggle - Should work
- [ ] Password strength indicator - Should update in real-time
- [ ] Form validation on blur - Should show errors
- [ ] Form validation on submit - Should prevent submission
- [ ] Remember me checkbox - Should set cookie
- [ ] Logout - Should destroy session

## Troubleshooting

### Database Connection Error
```
Connection Error: SQLSTATE[HY000] [1049] Unknown database 'electronics_store'
```
**Solution**: Run `database/setup.sql` to create the database

### Password Not Hashing
**Check**: PHP version must be 5.5+ for `password_hash()`

### Validation Not Working
**Check**: 
1. JavaScript file is loaded: `assets/js/auth-validation.js`
2. Browser console for errors
3. Form ID matches: `registerForm` or `loginForm`

### Session Not Persisting
**Check**:
1. `session_start()` is called at top of file
2. No output before `session_start()`
3. PHP session directory is writable

## Next Steps

1. **Email Verification**: Add email verification system
2. **Password Reset**: Implement forgot password functionality
3. **Profile Page**: Create user profile management
4. **Admin Panel**: Build admin dashboard
5. **OAuth Integration**: Add Google/Facebook login
6. **Two-Factor Auth**: Implement 2FA for security

## Support

For issues or questions:
- Email: info@electrohub.com
- Documentation: Check README.md

---

**Authentication System v1.0**
Built with PHP OOP, PDO, and modern JavaScript

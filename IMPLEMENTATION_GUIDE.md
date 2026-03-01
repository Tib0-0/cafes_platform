# Implementation Guide

## Overview
This guide shows you how to migrate from the old procedural code to the new OOP architecture.

## Key Changes

### Before (Old Procedural Code)
```php
<?php
require_once "../config/database.php";

$email = $_POST['email'];
$password = $_POST['password'];

$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!password_verify($password, $user['password_hash'])) {
    echo "Invalid password";
    exit;
}

$_SESSION['user_id'] = $user['user_id'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];
header("Location: dashboard.php");
?>
```

### After (New OOP Code)
```php
<?php
require_once "../core/bootstrap.php";

$email = RequestValidator::post('email');
$password = RequestValidator::post('password');

$userService = new UserService();
$user = $userService->login($email, $password);

if (!$user) {
    ResponseHelper::error("Login failed", [$userService->getLastError()]);
}

AuthHelper::setSession('user_id', $user['user_id']);
AuthHelper::setSession('email', $user['email']);
AuthHelper::setSession('role', $user['role']);
ResponseHelper::redirect('dashboard.php');
?>
```

## Step-by-Step Migration

### Step 1: Update Your HTML Forms
Update form actions to point to refactored files:

```html
<!-- Before -->
<form action="../backend/C4F3_login.php" method="POST">

<!-- After -->
<form action="../backend/C4F3_login_refactored.php" method="POST">
```

### Step 2: Update JavaScript AJAX Calls
```javascript
// Before
fetch('../backend/get_products.php')
  .then(r => r.json())
  .then(data => console.log(data));

// After
fetch('../backend/get_products_refactored.php')
  .then(r => r.json())
  .then(data => console.log(data));
```

### Step 3: Handle Response Format
The refactored endpoints return JSON in a consistent format:

```json
{
  "success": true,
  "message": "Success message",
  "data": { /* actual data */ }
}
```

Or for errors:
```json
{
  "success": false,
  "message": "Error occurred",
  "errors": ["Error details..."]
}
```

Update your JavaScript to handle this:
```javascript
fetch('../backend/C4F3_login_refactored.php', {
  method: 'POST',
  body: new FormData(form)
})
.then(r => r.json())
.then(data => {
  if (data.success) {
    console.log("Success:", data.message);
    window.location.href = data.redirect; // if included
  } else {
    console.error("Error:", data.errors);
  }
});
```

## File Migration Map

| Old File | New File | Status |
|----------|----------|--------|
| C4F3_login.php | C4F3_login_refactored.php | ✓ Ready |
| C4F3_Registration.php | C4F3_Registration_refactored.php | ✓ Ready |
| get_products.php | get_products_refactored.php | ✓ Ready |
| get_categories.php | get_categories_refactored.php | ✓ Ready |
| get_product.php | get_product_refactored.php | ✓ Ready |
| C4F3_create_product_ad.php | C4F3_create_product_ad_refactored.php | ✓ Ready |
| admin/get_users.php | admin/get_users_refactored.php | ✓ Ready |
| admin/approve_product.php | admin/approve_product_refactored.php | ✓ Ready |
| admin/reject_product.php | admin/reject_product_refactored.php | ✓ Ready |
| admin/toggle_user_status.php | admin/toggle_user_status_refactored.php | ✓ Ready |
| admin/get_product.php | admin/get_product_refactored.php | ✓ Ready |
| admin/get_partnership.php | admin/get_partnership_refactored.php | ✓ Ready |
| partnership/create_request.php | partnership/create_request_refactored.php | ✓ Ready |

## Core Classes Directory

All new OOP classes are in `core/` folder:

```
core/
├── bootstrap.php                 # Include this in all files
├── Autoloader.php               # Auto-loads classes
├── ServiceInterface.php          # Base interface for all services
├── BaseService.php              # Abstract service base class
├── BaseRepository.php           # Abstract repository base class
├── Validator.php                # Input validation
├── UserRepository.php           # User database operations
├── UserService.php              # User business logic
├── ProductRepository.php        # Product database operations
├── ProductService.php           # Product business logic
├── PartnershipRepository.php    # Partnership database operations
└── PartnershipService.php       # Partnership business logic
```

## Usage in Your Files

### For API Endpoints
```php
<?php
header('Content-Type: application/json');
require_once "../core/bootstrap.php";

try {
    // Your logic here
    $service = new ProductService();
    $data = $service->getApprovedProducts();
    
    ResponseHelper::json($data);
} catch (Exception $e) {
    ResponseHelper::error("Server error", [$e->getMessage()], 500);
}
?>
```

### For Protected Pages
```php
<?php
require_once "../core/bootstrap.php";

// Require authentication
AuthHelper::requireLogin();

// Require admin role
AuthHelper::requireAdmin();

// Your logic here
$userId = AuthHelper::userId();
// ...
?>
```

### For Forms/Redirects
```php
<?php
require_once "../core/bootstrap.php";

if (!RequestValidator::isPost()) {
    ResponseHelper::error("Invalid request", [], 405);
}

$email = RequestValidator::post('email', '');
if (empty($email)) {
    ResponseHelper::error("Email required", ["Email is missing"], 400);
}

// Process data
// ...

ResponseHelper::redirect('../pages/success.html');
?>
```

## Testing the Implementation

### 1. Test Home Page (No Auth Required)
```
GET /backend/get_products_refactored.php
```
Should return JSON list of approved products

### 2. Test Login
```
POST /backend/C4F3_login_refactored.php
Body: email=vendor@example.com&password=TestPass123
```
Should redirect on success or return error

### 3. Test Protected Endpoint (Admin Only)
```
GET /backend/admin/get_users_refactored.php

With session:
- user_id: 1
- role: admin
```
Should return list of users or error if not admin

### 4. Test Protected Endpoint (Vendor Only)
```
POST /backend/C4F3_create_product_ad_refactored.php

With session:
- user_id: 5
- role: vendor

Body: product_name=Coffee&price=150&category=Beverages
```
Should create product or error if not vendor

## Common Issues and Solutions

### Issue 1: Classes Not Found
**Solution:** Make sure you're including `core/bootstrap.php` at the top of your file

```php
require_once "../core/bootstrap.php"; // Always include this
```

### Issue 2: Session Lost
**Solution:** Bootstrap automatically handles session. Don't call `session_start()` again

```php
// ✓ Correct - Bootstrap handles it
require_once "../core/bootstrap.php";

// ✗ Wrong - Don't do this
session_start();
require_once "../core/bootstrap.php";
```

### Issue 3: Authentication Errors
**Solution:** Make sure refactored files exist and old files use new auth flow

```php
// ✓ Correct
AuthHelper::requireVendor();
$vendorId = AuthHelper::userId();

// ✗ Wrong
if (!isset($_SESSION['user_id'])) { ... }
```

### Issue 4: JSON Response Issues
**Solution:** Use ResponseHelper for consistent JSON responses

```php
// ✓ Correct
header('Content-Type: application/json');
require_once "../core/bootstrap.php";
ResponseHelper::json($data);

// ✗ Wrong
echo json_encode($data);
```

## Gradual Migration Path

### Phase 1: Setup (Week 1)
- [ ] Copy all files from `core/` folder
- [ ] Update include statements in test files
- [ ] Verify bootstrap.php loads without errors

### Phase 2: Test Files (Week 1-2)
- [ ] Test refactored login endpoint
- [ ] Test refactored registration endpoint
- [ ] Test public API endpoints (get_products)

### Phase 3: Admin Files (Week 2)
- [ ] Test admin user management
- [ ] Test product approval/rejection
- [ ] Test partnership management

### Phase 4: Core Pages (Week 3)
- [ ] Update HTML forms to use refactored endpoints
- [ ] Update JavaScript AJAX calls
- [ ] Test all user flows

### Phase 5: Cleanup (Week 4)
- [ ] Archive old files
- [ ] Remove old endpoints
- [ ] Update documentation

## Benefits After Migration

1. **Security:** Encapsulation prevents direct database access
2. **Maintainability:** Code is organized in services/repositories
3. **Reusability:** Services can be used in multiple files
4. **Consistency:** All endpoints follow same pattern
5. **Error Handling:** Centralized error management
6. **Validation:** Consistent input validation
7. **Scalability:** Easy to add new features

## Support

For detailed examples, see:
- `QUICK_START.md` - Quick reference
- `OOP_ARCHITECTURE.md` - Architecture details
- `tests/OOPArchitectureTest.php` - Test suite


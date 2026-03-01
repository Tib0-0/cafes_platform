# Quick Start Guide - OOP Architecture

## Installation

1. **All new OOP classes are in the `core/` folder**
2. **Refactored API files are in `backend/` with `_refactored` suffix**

## Quick Examples

### 1. Login User
```php
<?php
require_once "../core/bootstrap.php";

if (RequestValidator::isPost()) {
    $email = RequestValidator::post('email');
    $password = RequestValidator::post('password');
    
    $userService = new UserService();
    $user = $userService->login($email, $password);
    
    if ($user) {
        AuthHelper::setSession('user_id', $user['user_id']);
        AuthHelper::setSession('email', $user['email']);
        AuthHelper::setSession('role', $user['role']);
        ResponseHelper::redirect('../pages/dashboard.html');
    } else {
        ResponseHelper::error("Login failed", [$userService->getLastError()]);
    }
}
?>
```

### 2. Register User
```php
<?php
require_once "../core/bootstrap.php";

$userService = new UserService();
$userId = $userService->register([
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'password' => 'SecurePass123',
    'role' => 'vendor'
]);

if ($userId) {
    ResponseHelper::success(['user_id' => $userId]);
} else {
    ResponseHelper::error("Registration failed", $userService->getErrors());
}
?>
```

### 3. Create Product (Vendor Only)
```php
<?php
require_once "../core/bootstrap.php";

AuthHelper::requireVendor();

$productService = new ProductService();
$productId = $productService->createProduct([
    'vendor_id' => AuthHelper::userId(),
    'product_name' => 'Espresso Coffee',
    'description' => 'Rich and aromatic',
    'price' => 150.00,
    'category' => 'Beverages',
    'image_url' => '/uploads/products/espresso.jpg'
]);

if ($productId) {
    ResponseHelper::success(['product_id' => $productId]);
} else {
    ResponseHelper::error("Failed to create product", $productService->getErrors());
}
?>
```

### 4. Get Approved Products (Public API)
```php
<?php
header('Content-Type: application/json');
require_once "../core/bootstrap.php";

$productService = new ProductService();
$products = $productService->getApprovedProducts();
ResponseHelper::json($products);
?>
```

### 5. Admin: Approve Product
```php
<?php
require_once "../core/bootstrap.php";

AuthHelper::requireAdmin();

$productId = RequestValidator::get('product_id');
$productService = new ProductService();
$success = $productService->approveProduct($productId);

if ($success) {
    ResponseHelper::success(null, "Product approved");
} else {
    ResponseHelper::error("Failed to approve", $productService->getErrors());
}
?>
```

### 6. Get Vendor's Products
```php
<?php
require_once "../core/bootstrap.php";

AuthHelper::requireVendor();
$vendorId = AuthHelper::userId();

$productService = new ProductService();
$products = $productService->getVendorProducts($vendorId);
ResponseHelper::json($products);
?>
```

### 7. Create Partnership Request
```php
<?php
require_once "../core/bootstrap.php";

$partnershipService = new PartnershipService();
$partnerId = $partnershipService->createRequest([
    'vendor_id' => RequestValidator::post('vendor_id'),
    'cafe_owner_id' => RequestValidator::post('cafe_owner_id'),
    'message' => RequestValidator::post('message')
]);

if ($partnerId) {
    ResponseHelper::success(['partnership_id' => $partnerId]);
} else {
    ResponseHelper::error("Failed to create request", $partnershipService->getErrors());
}
?>
```

### 8. Admin: Get Users
```php
<?php
require_once "../core/bootstrap.php";

AuthHelper::requireAdmin();

$userService = new UserService();
$users = $userService->getAll();

// Remove password hashes
foreach ($users as &$user) {
    unset($user['password_hash']);
}

ResponseHelper::json($users);
?>
```

### 9. Admin: Toggle User Status
```php
<?php
require_once "../core/bootstrap.php";

AuthHelper::requireAdmin();

$userId = RequestValidator::post('user_id');
$status = RequestValidator::post('status'); // 0 or 1

$userService = new UserService();
$success = $userService->toggleUserStatus($userId, $status);

if ($success) {
    ResponseHelper::success(null, "User status updated");
} else {
    ResponseHelper::error("Failed to update status", $userService->getErrors());
}
?>
```

## Authentication Helper Methods

```php
// Check if logged in
if (AuthHelper::isLoggedIn()) { }

// Check user role
if (AuthHelper::isAdmin()) { }
if (AuthHelper::isVendor()) { }
if (AuthHelper::isCafeOwner()) { }

// Get user info
$userId = AuthHelper::userId();
$email = AuthHelper::userEmail();
$role = AuthHelper::userRole();

// Require authentication
AuthHelper::requireLogin();   // Redirects if not logged in
AuthHelper::requireAdmin();   // Redirects if not admin
AuthHelper::requireVendor();  // Redirects if not vendor

// Logout
AuthHelper::logout();
```

## Request Helper Methods

```php
// Check request method
if (RequestValidator::isPost()) { }
if (RequestValidator::isGet()) { }

// Get parameters
$email = RequestValidator::post('email');
$id = RequestValidator::get('id');

// Check if parameter exists
if (RequestValidator::hasPost('email')) { }

// Get session
$userId = RequestValidator::session('user_id');
RequestValidator::setSession('key', 'value');
```

## Response Helper Methods

```php
// Send JSON
ResponseHelper::json($data);

// Send success response
ResponseHelper::success($data, "Message");

// Send error response
ResponseHelper::error("Error message", ['error1', 'error2']);

// Redirect
ResponseHelper::redirect('../pages/login.html');
```

## Service Methods

### UserService
```php
$userService = new UserService();

// Register new user
$userId = $userService->register($userData);

// Login user
$user = $userService->login($email, $password);

// Get user by ID
$user = $userService->getById($userId);

// Get all users
$users = $userService->getAll();

// Get users by role
$vendors = $userService->getUsersByRole('vendor');

// Get active users by role
$activeVendors = $userService->getActiveByRole('vendor');

// Toggle user status
$userService->toggleUserStatus($userId, 1); // 1 = active, 0 = inactive

// Get errors
$errors = $userService->getErrors();
$lastError = $userService->getLastError();
```

### ProductService
```php
$productService = new ProductService();

// Create product
$productId = $productService->createProduct($data);

// Get product by ID
$product = $productService->getById($productId);

// Get all approved products
$products = $productService->getApprovedProducts();

// Get vendor's products
$vendorProducts = $productService->getVendorProducts($vendorId);

// Get products by status
$pending = $productService->getProductsByStatus('pending');

// Get products by category
$coffees = $productService->getProductsByCategory('Coffee');

// Get categories
$categories = $productService->getCategories();

// Approve product
$productService->approveProduct($productId);

// Reject product
$productService->rejectProduct($productId);

// Get errors
$errors = $productService->getErrors();
```

### PartnershipService
```php
$partnershipService = new PartnershipService();

// Create partnership request
$partnerId = $partnershipService->createRequest($data);

// Get vendor's requests
$requests = $partnershipService->getVendorRequests($vendorId);

// Get owner's requests
$requests = $partnershipService->getOwnerRequests($ownerId);

// Get requests by status
$pending = $partnershipService->getRequestsByStatus('pending');

// Approve request
$partnershipService->approveRequest($partnerId);

// Reject request
$partnershipService->rejectRequest($partnerId);

// Get errors
$errors = $partnershipService->getErrors();
```

## Common Patterns

### Pattern 1: CRUD with Error Handling
```php
$service = new ProductService();
$id = $service->create($data);

if (!$id) {
    $errors = $service->getErrors();
    ResponseHelper::error("Creation failed", $errors, 400);
    return;
}

ResponseHelper::success(['id' => $id]);
```

### Pattern 2: Admin Action with Authentication
```php
AuthHelper::requireAdmin();

$service = new ProductService();
$success = $service->approveProduct($productId);

if (!$success) {
    ResponseHelper::error("Action failed", $service->getErrors(), 400);
}

ResponseHelper::success(null, "Action completed");
```

### Pattern 3: User-Specific Data
```php
AuthHelper::requireLogin();

$userId = AuthHelper::userId();
$service = new ProductService();
$products = $service->getVendorProducts($userId);

ResponseHelper::json($products);
```

## Validator Usage

```php
$validator = new Validator();

// Email validation
if (!$validator->email($email)) {
    $errors = $validator->getErrors();
}

// Password validation
if (!$validator->password($password)) {
    // Password must be 8+ chars, contain uppercase, lowercase, numbers
}

// Required field
$validator->required($value, 'Field Name');

// Numeric check
$validator->numeric($value, 'Field Name');

// Max length
$validator->maxLength($value, 100, 'Field Name');

// Value in array
$validator->inArray($value, ['admin', 'vendor', 'user'], 'Role');

// Get all errors
$errors = $validator->getErrors();

// Clear errors
$validator->clearErrors();

// Check if valid
if ($validator->isValid()) { }
```

## Error Handling Example

```php
<?php
require_once "../core/bootstrap.php";

try {
    $userService = new UserService();
    $userId = $userService->register([
        'username' => 'test',
        'email' => 'test@example.com',
        'password' => 'TestPass123',
        'role' => 'vendor'
    ]);

    if (!$userId) {
        $errors = $userService->getErrors();
        ResponseHelper::error("Registration failed", $errors, 400);
    }

    ResponseHelper::success(['user_id' => $userId], "Registration successful");

} catch (PDOException $e) {
    ResponseHelper::error("Database error", [$e->getMessage()], 500);
} catch (Exception $e) {
    ResponseHelper::error("Server error", [$e->getMessage()], 500);
}
?>
```

## File Naming Convention

- **Old files:** `C4F3_login.php`, `get_products.php`
- **New files:** `C4F3_login_refactored.php`, `get_products_refactored.php`

Gradually replace old files with refactored versions once tested.

## Next Steps

1. Test one refactored endpoint
2. Update your frontend to call refactored endpoints
3. Migrate remaining endpoints
4. Delete old files when verified all functionality works


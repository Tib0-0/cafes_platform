# OOP Architecture Documentation

## Overview
This refactored codebase implements robust Object-Oriented Programming (OOP) principles including **Encapsulation**, **Polymorphism**, **Inheritance**, and **Abstraction**.

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                      Bootstrap (core/bootstrap.php)          │
│  - Initializes application                                   │
│  - Loads configuration and autoloader                        │
│  - Provides helper classes (AuthHelper, ResponseHelper, etc) │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                    Services (Business Logic)                 │
│                                                              │
│  ┌──────────────────────────────────────────────────────┐  │
│  │ BaseService (Abstract)                               │  │
│  │ - Implements ServiceInterface                        │  │
│  │ - Provides common validation, sanitization          │  │
│  │ - Demonstrates: Inheritance, Abstraction            │  │
│  └──────────────────────────────────────────────────────┘  │
│         ↓                    ↓                     ↓         │
│  ┌────────────────┐  ┌────────────────┐  ┌─────────────┐  │
│  │ UserService    │  │ProductService  │  │Partnership  │  │
│  │                │  │                │  │Service      │  │
│  │ - register()   │  │ - createProduct│  │             │  │
│  │ - login()      │  │ - getApproved()│  │ -create     │  │
│  │ - toggleStatus │  │ - approveProduct              │  │
│  │                │  │ - getCategories│  │ Request()  │  │
│  └────────────────┘  └────────────────┘  └─────────────┘  │
│                                                              │
│  All extend BaseService, implement ServiceInterface        │
│  Demonstrate: Polymorphism, Encapsulation                 │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                 Repositories (Data Access)                   │
│                                                              │
│  ┌──────────────────────────────────────────────────────┐  │
│  │ BaseRepository (Abstract)                            │  │
│  │ - findById(), findAll()                              │  │
│  │ - create(), update(), delete()                       │  │
│  │ - executeQuery() - protected method                  │  │
│  │ - Demonstrates: Inheritance, Encapsulation, Abstraction
│  └──────────────────────────────────────────────────────┘  │
│         ↓                    ↓                     ↓         │
│  ┌────────────────┐  ┌────────────────┐  ┌─────────────┐  │
│  │ UserRepository │  │ProductRepository │Partnership    │  │
│  │                │  │                │  │Repository  │  │
│  │ - findByEmail()│  │ - getCategories │ - findByVendor
│  │ - findByRole() │  │ - findApproved()│ - findByOwner  │  │
│  │ - toggleStatus │  │ - updateStatus()│ - updateStatus │  │
│  └────────────────┘  └────────────────┘  └─────────────┘  │
│                                                              │
│  All extend BaseRepository                                 │
│  Demonstrate: Inheritance, Encapsulation                  │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                    Database PDO Connection                   │
│              (Database class in config/database.php)         │
└─────────────────────────────────────────────────────────────┘
```

## Core Classes

### 1. **ServiceInterface** (Polymorphism Contract)
```php
interface ServiceInterface {
    public function getById($id);
    public function getAll();
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function getErrors();
}
```
**Demonstrates:** Polymorphism - All services must implement these methods

### 2. **BaseService** (Abstraction & Inheritance)
```php
abstract class BaseService implements ServiceInterface {
    protected $repository;
    protected $errors = [];
    protected $validator;
    
    abstract protected function validate(array $data);
    abstract protected function sanitize(array $data);
    // ... common methods
}
```
**Demonstrates:** 
- **Abstraction:** Abstract methods force child classes to implement validation
- **Inheritance:** Child services inherit common functionality
- **Encapsulation:** Protected properties and methods

### 3. **BaseRepository** (Abstraction & Inheritance)
```php
abstract class BaseRepository {
    protected $db;
    protected $table;
    protected $errors = [];
    
    public function findById($id) { /* ... */ }
    public function findAll() { /* ... */ }
    public function create(array $data) { /* ... */ }
    // ... CRUD operations
}
```
**Demonstrates:**
- **Inheritance:** All repositories inherit CRUD operations
- **Encapsulation:** Protected database connection and table name
- **Abstraction:** Common database operations

### 4. **Service Classes** (Polymorphism & Inheritance)
Each service (UserService, ProductService, PartnershipService):
- Extends BaseService
- Implements ServiceInterface
- Provides domain-specific business logic
- Must implement abstract validate() and sanitize() methods

**Example:** UserService
```php
class UserService extends BaseService {
    public function __construct() {
        parent::__construct(new UserRepository());
    }
    
    public function login($email, $password) { /* ... */ }
    public function register(array $data) { /* ... */ }
    
    protected function validate(array $data) { /* specific validation */ }
    protected function sanitize(array $data) { /* specific sanitization */ }
}
```

## Helper Classes

### AuthHelper
```php
class AuthHelper {
    public static function userId();
    public static function userRole();
    public static function isAdmin();
    public static function isVendor();
    public static function requireLogin();
    public static function requireAdmin();
}
```

### ResponseHelper
```php
class ResponseHelper {
    public static function json($data, $statusCode = 200);
    public static function success($data, $message = "Success");
    public static function error($message, $errors = [], $statusCode = 400);
    public static function redirect($path);
}
```

### RequestValidator
```php
class RequestValidator {
    public static function isPost();
    public static function post($key, $default = null);
    public static function hasPost($key);
    public static function session($key, $default = null);
}
```

### Validator
```php
class Validator {
    public function email($email);
    public function password($password);
    public function required($value, $fieldName);
    public function numeric($value, $fieldName);
    public function inArray($value, $allowedValues, $fieldName);
}
```

## Usage Examples

### Example 1: User Registration
```php
require_once "../core/bootstrap.php";

$userService = new UserService();
$userId = $userService->register([
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'password' => 'SecurePass123',
    'role' => 'vendor'
]);

if (!$userId) {
    $errors = $userService->getErrors();
    // Handle errors
}
```

### Example 2: Product Management
```php
require_once "../core/bootstrap.php";

$productService = new ProductService();

// Get approved products
$products = $productService->getApprovedProducts();

// Get vendor's products
$vendorProducts = $productService->getVendorProducts(5);

// Admin: get pending products
$pendingProducts = $productService->getProductsByStatus('pending');

// Admin: approve product
$success = $productService->approveProduct(42);
```

### Example 3: With Authentication Check
```php
require_once "../core/bootstrap.php";

// Only vendors can access
AuthHelper::requireVendor();

$vendorId = AuthHelper::userId();
$productService = new ProductService();
$myProducts = $productService->getVendorProducts($vendorId);
```

### Example 4: Error Handling
```php
require_once "../core/bootstrap.php";

$userService = new UserService();
$user = $userService->login('user@example.com', 'password123');

if (!$user) {
    $error = $userService->getLastError();
    ResponseHelper::error("Login failed", [$error], 401);
}
```

## File Structure

```
cafes_platform/
├── core/                          # OOP Implementation
│   ├── bootstrap.php              # Application bootstrap & helpers
│   ├── Autoloader.php             # Class autoloading
│   ├── ServiceInterface.php        # Service contract (polymorphism)
│   ├── BaseService.php            # Abstract service base class
│   ├── BaseRepository.php         # Abstract repository base class
│   ├── UserService.php            # User business logic
│   ├── UserRepository.php         # User database operations
│   ├── ProductService.php         # Product business logic
│   ├── ProductRepository.php      # Product database operations
│   ├── PartnershipService.php     # Partnership business logic
│   ├── PartnershipRepository.php  # Partnership database operations
│   └── Validator.php              # Input validation
│
├── backend/                       # Refactored API endpoints
│   ├── C4F3_login_refactored.php
│   ├── C4F3_Registration_refactored.php
│   ├── C4F3_create_product_ad_refactored.php
│   ├── get_products_refactored.php
│   ├── get_categories_refactored.php
│   ├── get_product_refactored.php
│   └── admin/
│       ├── get_users_refactored.php
│       ├── approve_product_refactored.php
│       ├── reject_product_refactored.php
│       ├── toggle_user_status_refactored.php
│       ├── get_product_refactored.php
│       └── get_partnership_refactored.php
│   └── partnership/
│       └── create_request_refactored.php
│
├── config/
│   ├── config.php
│   └── database.php
│
└── ... other files
```

## OOP Principles Implemented

### 1. **Encapsulation**
- Private properties in Database class
- Protected properties in BaseRepository and BaseService
- Data hiding and controlled access through public methods
- Error handling internal to classes

### 2. **Inheritance**
- UserService, ProductService, PartnershipService extend BaseService
- UserRepository, ProductRepository, PartnershipRepository extend BaseRepository
- Child classes extend parent functionality
- Code reuse through inheritance hierarchy

### 3. **Polymorphism**
- All services implement ServiceInterface
- Different services (User, Product, Partnership) provide different implementations
- Same method names (create, update, delete) behave differently per service
- Validator class provides multiple validation methods with same pattern

### 4. **Abstraction**
- BaseService and BaseRepository are abstract
- Abstract methods force child classes to implement domain-specific logic
- Complex database operations hidden in repositories
- Business logic abstracted in services

## Migration Guide

### Step 1: Update Your Includes
Change from:
```php
require_once "../config/database.php";
$db = (new Database())->getConnection();
```

To:
```php
require_once "../core/bootstrap.php";
$userService = new UserService();
```

### Step 2: Use Services Instead of Direct Queries
**Before:**
```php
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();
```

**After:**
```php
$userService = new UserService();
$user = $userService->login($email, $password);
```

### Step 3: Use Helper Classes
**Before:**
```php
$_SESSION['user_id'] = $row['user_id'];
if (isset($_POST['email'])) { }
```

**After:**
```php
AuthHelper::setSession('user_id', $user['user_id']);
if (RequestValidator::hasPost('email')) { }
```

### Step 4: Use ResponseHelper for API Responses
**Before:**
```php
header('Content-Type: application/json');
echo json_encode($data);
```

**After:**
```php
ResponseHelper::json($data);
// or
ResponseHelper::success($data, "Operation successful");
ResponseHelper::error("Operation failed", $errors);
```

## Testing the Implementation

### Test User Registration
```php
$userService = new UserService();
$userId = $userService->register([
    'username' => 'testvendor',
    'email' => 'test@example.com',
    'password' => 'TestPass123',
    'role' => 'vendor'
]);

if ($userId) {
    echo "Registration successful: " . $userId;
} else {
    print_r($userService->getErrors());
}
```

### Test Product Creation
```php
AuthHelper::setSession('user_id', 1);
AuthHelper::setSession('role', 'vendor');

$productService = new ProductService();
$productId = $productService->createProduct([
    'vendor_id' => 1,
    'product_name' => 'Espresso',
    'description' => 'Rich and bold',
    'price' => 150,
    'category' => 'Coffee'
]);

if ($productId) {
    echo "Product created: " . $productId;
}
```

## Advantages of This Architecture

1. **Maintainability:** Code is organized and easy to understand
2. **Reusability:** Services can be used across multiple files
3. **Testability:** Each service can be tested independently
4. **Scalability:** Easy to add new services/repositories
5. **Security:** Encapsulation prevents direct database access
6. **Consistency:** All files follow the same pattern
7. **Error Handling:** Centralized error management
8. **Validation:** Consistent validation logic across all services

## Next Steps

1. Replace old files with refactored versions
2. Update frontend to use refactored API endpoints
3. Add unit tests for services
4. Implement additional services as needed
5. Consider adding a controller layer for more complex routing


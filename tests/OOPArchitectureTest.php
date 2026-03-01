<?php
/**
 * OOP Architecture Test Suite
 * Demonstrates all OOP principles in action
 * 
 * To run: php tests/OOPArchitectureTest.php
 */

require_once __DIR__ . '/../core/bootstrap.php';

class OOPArchitectureTest {
    
    private $results = [];
    private $testCount = 0;
    private $passCount = 0;

    public function run() {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "OOP ARCHITECTURE TEST SUITE\n";
        echo str_repeat("=", 60) . "\n\n";

        // Test organization
        echo "Testing Encapsulation...\n";
        $this->testEncapsulation();

        echo "\nTesting Inheritance...\n";
        $this->testInheritance();

        echo "\nTesting Polymorphism...\n";
        $this->testPolymorphism();

        echo "\nTesting Abstraction...\n";
        $this->testAbstraction();

        // Print summary
        $this->printSummary();
    }

    /**
     * Test 1: Encapsulation
     * Protected properties prevent direct access
     */
    private function testEncapsulation() {
        try {
            // BaseRepository has protected properties
            $repo = new UserRepository();
            
            // Verify protected properties exist (through reflection)
            $reflection = new ReflectionClass($repo);
            $properties = $reflection->getProperties(ReflectionProperty::IS_PROTECTED);
            
            $protected = [];
            foreach ($properties as $prop) {
                $protected[] = $prop->getName();
            }

            // Should have protected properties: db, table, errors
            $hasProtected = count($protected) > 0;
            
            $this->test("Encapsulation: Protected properties in BaseRepository", $hasProtected);
            
            // Verify methods are public/protected appropriately
            $methods = $reflection->getMethods();
            $publicMethods = [];
            foreach ($methods as $method) {
                if ($method->isPublic() && !$method->isStatic()) {
                    $publicMethods[] = $method->getName();
                }
            }
            
            $this->test("Encapsulation: Public methods available", in_array('findById', $publicMethods));
            
            // Verify executeQuery is protected (internal use only)
            $hasProtectedMethod = false;
            foreach ($reflection->getMethods(ReflectionMethod::IS_PROTECTED) as $method) {
                if ($method->getName() === 'executeQuery') {
                    $hasProtectedMethod = true;
                }
            }
            
            $this->test("Encapsulation: Protected executeQuery method", $hasProtectedMethod);

        } catch (Exception $e) {
            $this->test("Encapsulation test", false);
            echo "Error: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Test 2: Inheritance
     * Child classes inherit parent functionality
     */
    private function testInheritance() {
        try {
            // UserService extends BaseService
            $userService = new UserService();
            $this->test("Inheritance: UserService extends BaseService", 
                        $userService instanceof BaseService);

            // ProductService extends BaseService
            $productService = new ProductService();
            $this->test("Inheritance: ProductService extends BaseService", 
                        $productService instanceof BaseService);

            // PartnershipService extends BaseService
            $partnershipService = new PartnershipService();
            $this->test("Inheritance: PartnershipService extends BaseService", 
                        $partnershipService instanceof BaseService);

            // UserRepository extends BaseRepository
            $userRepo = new UserRepository();
            $this->test("Inheritance: UserRepository extends BaseRepository", 
                        $userRepo instanceof BaseRepository);

            // All services inherit getAll(), getById(), etc.
            $hasInheritedMethod = method_exists($userService, 'getAll') 
                                && method_exists($userService, 'getById');
            $this->test("Inheritance: Services inherit CRUD methods", $hasInheritedMethod);

        } catch (Exception $e) {
            $this->test("Inheritance test", false);
            echo "Error: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Test 3: Polymorphism
     * Different services implement same interface differently
     */
    private function testPolymorphism() {
        try {
            // All services implement ServiceInterface
            $userService = new UserService();
            $productService = new ProductService();
            $partnershipService = new PartnershipService();

            $this->test("Polymorphism: UserService implements ServiceInterface", 
                        $userService instanceof ServiceInterface);
            $this->test("Polymorphism: ProductService implements ServiceInterface", 
                        $productService instanceof ServiceInterface);
            $this->test("Polymorphism: PartnershipService implements ServiceInterface", 
                        $partnershipService instanceof ServiceInterface);

            // Each service maintains same interface
            $services = [$userService, $productService, $partnershipService];
            $interfaceMethods = ['getById', 'getAll', 'create', 'update', 'delete', 'getErrors'];
            
            $allHaveMethods = true;
            foreach ($services as $service) {
                foreach ($interfaceMethods as $method) {
                    if (!method_exists($service, $method)) {
                        $allHaveMethods = false;
                        break;
                    }
                }
            }
            
            $this->test("Polymorphism: All services implement required methods", $allHaveMethods);

            // Different services have different specific methods
            $hasUserLogin = method_exists($userService, 'login');
            $hasProductApprove = method_exists($productService, 'approveProduct');
            $hasPartnershipApprove = method_exists($partnershipService, 'approveRequest');
            
            $this->test("Polymorphism: Each service has unique domain methods", 
                        $hasUserLogin && $hasProductApprove && $hasPartnershipApprove);

        } catch (Exception $e) {
            $this->test("Polymorphism test", false);
            echo "Error: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Test 4: Abstraction
     * Abstract classes force implementations
     */
    private function testAbstraction() {
        try {
            // BaseService is abstract
            $reflection = new ReflectionClass('BaseService');
            $this->test("Abstraction: BaseService is abstract", $reflection->isAbstract());

            // BaseRepository is abstract
            $reflection = new ReflectionClass('BaseRepository');
            $this->test("Abstraction: BaseRepository is abstract", $reflection->isAbstract());

            // BaseService has abstract methods
            $baseServiceReflection = new ReflectionClass('BaseService');
            $abstractMethods = [];
            foreach ($baseServiceReflection->getMethods(ReflectionMethod::IS_ABSTRACT) as $method) {
                $abstractMethods[] = $method->getName();
            }

            $hasAbstractMethods = count($abstractMethods) > 0;
            $this->test("Abstraction: BaseService has abstract methods", $hasAbstractMethods);

            // Child services must implement abstract methods
            $userService = new UserService();
            $userServiceReflection = new ReflectionClass($userService);
            
            // No abstract methods should remain in concrete class
            $concreteAbstractMethods = [];
            foreach ($userServiceReflection->getMethods(ReflectionMethod::IS_ABSTRACT) as $method) {
                $concreteAbstractMethods[] = $method->getName();
            }

            $this->test("Abstraction: Concrete classes implement all abstract methods", 
                        count($concreteAbstractMethods) === 0);

            // ServiceInterface provides abstract contract
            $interfaceReflection = new ReflectionClass('ServiceInterface');
            $this->test("Abstraction: ServiceInterface is interface", $interfaceReflection->isInterface());

        } catch (Exception $e) {
            $this->test("Abstraction test", false);
            echo "Error: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Record test result
     */
    private function test($description, $result) {
        $this->testCount++;
        
        if ($result) {
            $this->passCount++;
            $status = "✓ PASS";
            $color = "\033[92m"; // Green
        } else {
            $status = "✗ FAIL";
            $color = "\033[91m"; // Red
        }
        
        echo $color . $status . "\033[0m: " . $description . "\n";
    }

    /**
     * Print test summary
     */
    private function printSummary() {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "TEST SUMMARY\n";
        echo str_repeat("=", 60) . "\n";
        echo "Total Tests: " . $this->testCount . "\n";
        echo "Passed: " . $this->passCount . "\n";
        echo "Failed: " . ($this->testCount - $this->passCount) . "\n";
        
        $percentage = ($this->passCount / $this->testCount) * 100;
        echo "Success Rate: " . round($percentage, 2) . "%\n";
        echo str_repeat("=", 60) . "\n\n";

        if ($this->passCount === $this->testCount) {
            echo "✓ All tests passed! OOP architecture is properly implemented.\n\n";
        }
    }
}

// Run tests
$test = new OOPArchitectureTest();
$test->run();
?>

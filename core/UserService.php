<?php
/**
 * User Service Class
 * Extends: BaseService
 * Demonstrates: Polymorphism, Inheritance, Encapsulation, Abstraction
 * Handles user business logic
 */

class UserService extends BaseService {
    
    public function __construct() {
        parent::__construct(new UserRepository());
    }

    /**
     * Register new user
     * @param array $data
     * @return int|bool user_id or false
     */
    public function register(array $data) {
        // Validate
        if (!$this->validator->email($data['email'] ?? '')) {
            $this->addError($this->validator->getErrors()[0] ?? "Invalid email");
            return false;
        }

        if (!$this->validator->password($data['password'] ?? '')) {
            $this->errors = array_merge($this->errors, $this->validator->getErrors());
            return false;
        }

        // Check if email exists
        if ($this->repository->emailExists($data['email'])) {
            $this->addError("Email already registered");
            return false;
        }

        // Prepare data
        $userData = [
            'username' => $data['username'] ?? $data['business_name'] ?? '',
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'],
            'is_active' => 1
        ];

        return $this->repository->create($userData);
    }

    /**
     * Login user
     * @param string $email
     * @param string $password
     * @return array|null user data or null
     */
    public function login($email, $password) {
        if (!$this->validator->email($email)) {
            $this->addError("Invalid email format");
            return null;
        }

        $user = $this->repository->findByEmail($email);
        
        if (!$user) {
            $this->addError("Account not found");
            return null;
        }

        if ((int)$user['is_active'] !== 1) {
            $this->addError("Account is disabled");
            return null;
        }

        if (!password_verify($password, $user['password_hash'])) {
            $this->addError("Invalid password");
            return null;
        }

        // Return user without password hash
        unset($user['password_hash']);
        return $user;
    }

    /**
     * Get users by role
     * @param string $role
     * @return array
     */
    public function getUsersByRole($role) {
        return $this->repository->findByRole($role);
    }

    /**
     * Get active users by role
     * @param string $role
     * @return array
     */
    public function getActiveByRole($role) {
        return $this->repository->findActiveByRole($role);
    }

    /**
     * Toggle user status
     * @param int $userId
     * @param int $status
     * @return bool
     */
    public function toggleUserStatus($userId, $status) {
        if (!in_array($status, [0, 1])) {
            $this->addError("Invalid status value");
            return false;
        }
        return $this->repository->toggleStatus($userId, $status);
    }

    /**
     * Validate user data (override abstract method)
     */
    public function validate(array $data) {
        // Validation logic for generic user data
        return true;
    }

    /**
     * Sanitize user data (override abstract method)
     */
    public function sanitize(array $data) {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $sanitized[$key] = trim($value);
        }
        return $sanitized;
    }
}
?>

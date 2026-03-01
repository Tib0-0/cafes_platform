<?php
/**
 * Input Validator Class
 * Demonstrates: Encapsulation
 * Handles validation logic for different data types
 */

class Validator {
    
    private $errors = [];

    /**
     * Validate email
     */
    public function email($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Invalid email format";
            return false;
        }
        return true;
    }

    /**
     * Validate password strength
     */
    public function password($password) {
        if (strlen($password) < 8) {
            $this->errors[] = "Password must be at least 8 characters";
            return false;
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $this->errors[] = "Password must contain uppercase letters";
            return false;
        }
        if (!preg_match('/[a-z]/', $password)) {
            $this->errors[] = "Password must contain lowercase letters";
            return false;
        }
        if (!preg_match('/[0-9]/', $password)) {
            $this->errors[] = "Password must contain numbers";
            return false;
        }
        return true;
    }

    /**
     * Validate required field
     */
    public function required($value, $fieldName) {
        if (empty(trim($value))) {
            $this->errors[] = "{$fieldName} is required";
            return false;
        }
        return true;
    }

    /**
     * Validate string length
     */
    public function maxLength($value, $maxLength, $fieldName) {
        if (strlen($value) > $maxLength) {
            $this->errors[] = "{$fieldName} cannot exceed {$maxLength} characters";
            return false;
        }
        return true;
    }

    /**
     * Validate numeric value
     */
    public function numeric($value, $fieldName) {
        if (!is_numeric($value)) {
            $this->errors[] = "{$fieldName} must be numeric";
            return false;
        }
        return true;
    }

    /**
     * Validate in array
     */
    public function inArray($value, $allowedValues, $fieldName) {
        if (!in_array($value, $allowedValues)) {
            $this->errors[] = "{$fieldName} has invalid value";
            return false;
        }
        return true;
    }

    /**
     * Get all errors
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Clear errors
     */
    public function clearErrors() {
        $this->errors = [];
    }

    /**
     * Check if validation passed
     */
    public function isValid() {
        return count($this->errors) === 0;
    }
}
?>

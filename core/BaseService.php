<?php
/**
 * Base Service Class
 * Implements: ServiceInterface
 * Demonstrates: Abstraction, Inheritance, Encapsulation
 */

abstract class BaseService implements ServiceInterface {
    
    protected $repository;
    protected $errors = [];
    protected $validator;

    public function __construct($repository) {
        $this->repository = $repository;
        $this->validator = new Validator();
    }

    /**
     * Get single item
     */
    public function getById($id) {
        return $this->repository->findById($id);
    }

    /**
     * Get all items
     */
    public function getAll() {
        return $this->repository->findAll();
    }

    /**
     * Create item with validation
     */
    public function create(array $data) {
        if (!$this->validate($data)) {
            return false;
        }
        
        $data = $this->sanitize($data);
        return $this->repository->create($data);
    }

    /**
     * Update item with validation
     */
    public function update($id, array $data) {
        if (!$this->validate($data)) {
            return false;
        }
        
        $data = $this->sanitize($data);
        return $this->repository->update($id, $data);
    }

    /**
     * Delete item
     */
    public function delete($id) {
        return $this->repository->delete($id);
    }

    /**
     * Validate data (abstract - override in child classes)
     * @param array $data
     * @return bool
     */
    abstract public function validate(array $data);

    /**
     * Sanitize data (abstract - override in child classes)
     * @param array $data
     * @return array
     */
    abstract public function sanitize(array $data);

    /**
     * Get errors
     */
    public function getErrors() {
        $errors = $this->errors;
        $errors = array_merge($errors, $this->repository->getErrors());
        return $errors;
    }

    /**
     * Add error
     */
    protected function addError($error) {
        $this->errors[] = $error;
    }

    /**
     * Get last error
     */
    public function getLastError() {
        return end($this->errors) ?: $this->repository->getLastError();
    }
}
?>

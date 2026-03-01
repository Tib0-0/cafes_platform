<?php
/**
 * Service Interface
 * Demonstrates: Polymorphism (interface implementation)
 * Defines contract for all service classes
 */

interface ServiceInterface {
    
    /**
     * Get single item
     * @param int $id
     * @return array|null
     */
    public function getById($id);

    /**
     * Get all items
     * @return array
     */
    public function getAll();

    /**
     * Create item
     * @param array $data
     * @return bool|int
     */
    public function create(array $data);

    /**
     * Update item
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data);

    /**
     * Delete item
     * @param int $id
     * @return bool
     */
    public function delete($id);

    /**
     * Get validation errors
     * @return array
     */
    public function getErrors();
}
?>

<?php

namespace App\Repositories\Interfaces;

interface CookbookRepositoryInterface
{
    /**
     * Get all cookbooks.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all();

    /**
     * Get a cookbook by ID.
     *
     * @param  string  $id
     * @return \App\Models\Cookbook|null
     */
    public function find($id);

    /**
     * Create a new cookbook.
     *
     * @param  array  $data
     * @return \App\Models\Cookbook
     */
    public function create(array $data);

    /**
     * Update a cookbook.
     *
     * @param  string  $id
     * @param  array  $data
     * @return \App\Models\Cookbook
     */
    public function update($id, array $data);

    /**
     * Delete a cookbook.
     *
     * @param  string  $id
     * @return bool
     */
    public function delete($id);

    /**
     * Get cookbooks for a specific user.
     *
     * @param  string  $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserCookbooks($userId);

    /**
     * Get public cookbooks.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPublicCookbooks();
}

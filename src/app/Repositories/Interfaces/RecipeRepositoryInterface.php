<?php

namespace App\Repositories\Interfaces;

interface RecipeRepositoryInterface
{
    /**
     * Get all recipes.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all();

    /**
     * Get a recipe by ID.
     *
     * @param  string  $id
     * @return \App\Models\Recipe|null
     */
    public function find($id);

    /**
     * Create a new recipe.
     *
     * @return \App\Models\Recipe
     */
    public function create(array $data);

    /**
     * Update a recipe.
     *
     * @param  string  $id
     * @return \App\Models\Recipe
     */
    public function update($id, array $data);

    /**
     * Delete a recipe.
     *
     * @param  string  $id
     * @return bool
     */
    public function delete($id);

    /**
     * Get recipes for a specific user.
     *
     * @param  string  $userId
     * @param  int  $page
     * @param  int  $limit
     * @return array
     */
    public function getUserRecipes($userId, $page = 1, $limit = 10);

    /**
     * Get public recipes.
     *
     * @param  int  $page
     * @param  int  $limit
     * @return array
     */
    public function getPublicRecipes($page = 1, $limit = 10);

    /**
     * Search for recipes.
     *
     * @param  string  $query
     * @param  string|null  $userId
     * @param  int  $page
     * @param  int  $limit
     * @return array
     */
    public function searchRecipes($query, $userId = null, $page = 1, $limit = 10);
}

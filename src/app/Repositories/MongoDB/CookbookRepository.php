<?php

namespace App\Repositories\MongoDB;

use App\Models\Cookbook;
use App\Repositories\Interfaces\CookbookRepositoryInterface;

class CookbookRepository implements CookbookRepositoryInterface
{
    /**
     * Get all cookbooks.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return Cookbook::all();
    }

    /**
     * Get a cookbook by ID.
     *
     * @param  string  $id
     * @return \App\Models\Cookbook|null
     */
    public function find($id)
    {
        return Cookbook::find($id);
    }

    /**
     * Create a new cookbook.
     *
     * @return \App\Models\Cookbook
     */
    public function create(array $data)
    {
        return Cookbook::create($data);
    }

    /**
     * Update a cookbook.
     *
     * @param  string  $id
     * @return \App\Models\Cookbook
     */
    public function update($id, array $data)
    {
        $cookbook = $this->find($id);

        if (! $cookbook) {
            return null;
        }

        $cookbook->update($data);

        return $cookbook;
    }

    /**
     * Delete a cookbook.
     *
     * @param  string  $id
     * @return bool
     */
    public function delete($id)
    {
        $cookbook = $this->find($id);

        if (! $cookbook) {
            return false;
        }

        return $cookbook->delete();
    }

    /**
     * Get cookbooks for a specific user.
     *
     * @param  string  $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserCookbooks($userId)
    {
        return Cookbook::where('user_id', $userId)->get();
    }

    /**
     * Get public cookbooks.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPublicCookbooks()
    {
        return Cookbook::where('is_private', false)
            ->orWhereNull('is_private')
            ->get();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Source extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $collection = 'sources';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Get the recipes from this source.
     */
    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }
}

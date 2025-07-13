<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Classification extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'classifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Get the recipes with this classification.
     */
    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }
}

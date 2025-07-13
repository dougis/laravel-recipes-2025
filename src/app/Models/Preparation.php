<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Preparation extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'preparations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Get the recipes associated with this preparation method.
     */
    public function recipes()
    {
        return $this->belongsToMany(Recipe::class);
    }
}

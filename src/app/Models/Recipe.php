<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;
use MongoDB\Laravel\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory, Searchable;

    protected $connection = 'mongodb';

    protected $collection = 'recipes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'ingredients',
        'instructions',
        'notes',
        'servings',
        'source_id',
        'classification_id',
        'date_added',
        'calories',
        'fat',
        'cholesterol',
        'sodium',
        'protein',
        'marked',
        'tags',
        'meal_ids',
        'preparation_ids',
        'course_ids',
        'is_private',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_added' => 'datetime',
        'servings' => 'integer',
        'calories' => 'integer',
        'fat' => 'float',
        'cholesterol' => 'float',
        'sodium' => 'float',
        'protein' => 'float',
        'marked' => 'boolean',
        'tags' => 'array',
        'meal_ids' => 'array',
        'preparation_ids' => 'array',
        'course_ids' => 'array',
        'is_private' => 'boolean',
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
            'ingredients' => $this->ingredients,
            'instructions' => $this->instructions,
            'tags' => $this->tags,
            'user_id' => $this->user_id,
            'is_private' => $this->is_private ?? false,
        ];
    }

    /**
     * Get the user that owns the recipe.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the source for the recipe.
     */
    public function source()
    {
        return $this->belongsTo(Source::class);
    }

    /**
     * Get the classification for the recipe.
     */
    public function classification()
    {
        return $this->belongsTo(Classification::class);
    }

    /**
     * Get the meals associated with the recipe.
     */
    public function meals()
    {
        return $this->belongsToMany(Meal::class);
    }

    /**
     * Get the courses associated with the recipe.
     */
    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }

    /**
     * Get the preparations associated with the recipe.
     */
    public function preparations()
    {
        return $this->belongsToMany(Preparation::class);
    }

    /**
     * Get the cookbooks containing this recipe.
     */
    public function cookbooks()
    {
        return $this->belongsToMany(Cookbook::class);
    }
}

<?php

namespace App\Models;

use App\Core\Model;

class Recipe extends Model
{
    protected static string $table = 'recipes';
    protected static array $fillable = ['title', 'description', 'ingredients', 'prep_time', 'calories', 'image_url', 'tags'];
}

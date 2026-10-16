<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'icon', 'description'];

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}

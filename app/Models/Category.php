<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected $table = 'categories';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
        'featured_image',
        'status',
    ];

    protected $casts = [
        'status'      => 'boolean',
    ];

    public function products() 
    {
        return $this->hasMany(Product::class);
    }

    public function getExcerptAttribute() 
    {
        return substr($this->description, 0, 50);
    }

    public function getPublishedAtAttribute()
    {
        return $this->created_at->format('d/m/Y');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    /** @use HasFactory<\Database\Factories\ProductImageFactory> */
    use HasFactory;

    protected $table = 'product_images';

    protected $primaryKey = 'id';
    
    public $timestamps = true;

    protected $fillable = [
        'product_id',
        'image_url',
        'order',
        'is_featured'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'order' => 'integer'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

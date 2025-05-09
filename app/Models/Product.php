<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $table = 'products';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'category_id',
        'name',
        'sku,',
        'description',
        'purchase_price',
        'sale_price',
        'stock',
        'featured_image',
        'status',
    ];

    protected $casts = [
        'category_id'    => 'integer',
        'purchase_price' => 'decimal:2',
        'sale_price'     => 'decimal:2',
        'stock'          => 'integer',
        'status'         => 'boolean',
    ];

    public function category(){
        return $this->belongsTo(Category::class);
    }
}

<?php

namespace App\Filters;

use Illuminate\Http\Request;
use App\Filters\ApiFilter;

class ProductFilter extends ApiFilter {

    protected $safeParams = [
        'category_id' => ['eq'],
        'name' => ['eq', 'like'],
        'sku' => ['eq', 'lt', 'lte', 'gt', 'gte'],
        'description' => ['eq', 'like'],
        'purchase_price' => ['eq', 'lt', 'lte', 'gt', 'gte'],
        'price' => ['eq', 'lt', 'lte', 'gt', 'gte'],
        'stock' => ['eq', 'lt', 'lte', 'gt', 'gte'],
        'featured_image' => ['eq', 'like'], #
        'status' => ['eq', 'ne'],
        'created_at' => ['eq', 'lt', 'lte', 'gt', 'gte']
    ];
    protected $columnMap = [
        'price' => 'sale_price'
    ];
    protected $operatorMap = [
        'eq' => '=',
        'ne' => '!=',
        'like' => 'LIKE',
        'lt' => '<',
        'lte' => '<=',
        'gt' => '>',
        'gte' => '>='
    ];

}
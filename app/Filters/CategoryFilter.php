<?php

namespace App\Filters;

use Illuminate\Http\Request;
use App\Filters\ApiFilter;

class CategoryFilter extends ApiFilter {

    protected $safeParams = [
        'category_name' => ['eq', 'like'],
        'description_excerpt' => ['eq', 'like'],
        'url_image' => ['eq'],
        'created_at' => ['eq', 'lt', 'lte', 'gt', 'gte'],
    ];
    protected $columnMap = [
        'category_name' => 'name',
        'description_excerpt' => 'description',
        'url_image' => 'featured_image',
    ];
    protected $operatorMap = [
        'eq' => '=',
        'like' => 'LIKE',
        'lt' => '<',
        'lte' => '<=',
        'gt' => '>',
        'gte' => '>='
    ];

}
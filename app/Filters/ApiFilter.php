<?php

namespace App\Filters;

use Illuminate\Http\Request;
use Carbon\Carbon;

class ApiFilter
{
    protected $safeParams = [];
    protected $columnMap = [];
    protected $operatorMap = [];

    public function transform(Request $request)
    {
        $eloQuery = [];

        foreach ($this->safeParams as $param => $operators) {
            $query = $request->query($param);

            if (!isset($query)) {
                continue;
            }

            $column = $this->columnMap[$param] ?? $param;

            foreach ($operators as $operator) {
                if (isset($query[$operator])) {
                    $value = $query[$operator];

                    if (in_array($this->operatorMap[$operator], ['LIKE'])) {
                        $eloQuery[] = [$column, $this->operatorMap[$operator], "%{$value}%"];
                        continue;
                    }

                    if (in_array($column, ['created_at', 'updated_at'])) {
                        $operatorSymbol = $this->operatorMap[$operator];
                        $eloQuery[] = [function($query) use ($column, $value, $operatorSymbol) {
                            $query->whereDate($column, $operatorSymbol, $value);
                        }];
                        continue;
                    }
                    
                    $eloQuery[] = [$column, $this->operatorMap[$operator], $value];
                }
            }
        }

        return $eloQuery;
    }
}
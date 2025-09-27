<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class BaseFilter
{
    protected Request $request;

    protected Builder $builder;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;

        foreach ($this->filters() as $filter => $value) {
            if ($this->hasFilter($filter)) {
                $this->$filter($value);
            }
        }

        return $this->builder;
    }

    abstract public function filters(): array;

    protected function hasFilter(string $filter): bool
    {
        return method_exists($this, $filter) &&
               $this->request->filled($this->getParameterName($filter));
    }

    protected function getParameterName(string $filter): string
    {
        return $filter;
    }

    protected function getFilterValue(string $filter)
    {
        return $this->request->get($this->getParameterName($filter));
    }

    /**
     * Get the active filters as an array for service layer compatibility.
     */
    public function getActiveFilters(): array
    {
        $activeFilters = [];

        foreach ($this->filters() as $filter => $value) {
            if ($this->hasFilter($filter)) {
                $activeFilters[$filter] = $value;
            }
        }

        return $activeFilters;
    }
}

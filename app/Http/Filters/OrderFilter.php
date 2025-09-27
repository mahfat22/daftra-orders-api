<?php

namespace App\Http\Filters;

use App\Enums\OrderStatus;
use Carbon\Carbon;

class OrderFilter extends BaseFilter
{
    public function filters(): array
    {
        return [
            'status' => $this->getFilterValue('status'),
            'user_id' => $this->getFilterValue('user_id'),
            'total_min' => $this->getFilterValue('total_min'),
            'total_max' => $this->getFilterValue('total_max'),
            'date_from' => $this->getFilterValue('date_from'),
            'date_to' => $this->getFilterValue('date_to'),
            'search' => $this->getFilterValue('search'),
        ];
    }

    protected function status($value): void
    {
        if ($status = OrderStatus::tryFrom($value)) {
            $this->builder->where('status', $status);
        }
    }

    protected function user_id($value): void
    {
        $this->builder->where('user_id', $value);
    }

    protected function total_min($value): void
    {
        if (is_numeric($value)) {
            $this->builder->where('total', '>=', $value);
        }
    }

    protected function total_max($value): void
    {
        if (is_numeric($value)) {
            $this->builder->where('total', '<=', $value);
        }
    }

    protected function date_from($value): void
    {
        try {
            $date = Carbon::parse($value);
            $this->builder->where('created_at', '>=', $date);
        } catch (\Exception $e) {
        }
    }

    protected function date_to($value): void
    {
        try {
            $date = Carbon::parse($value)->endOfDay();
            $this->builder->where('created_at', '<=', $date);
        } catch (\Exception $e) {
        }
    }

    protected function search($value): void
    {
        $this->builder->where(function ($query) use ($value) {
            $query->where('id', 'like', "%{$value}%")
                ->orWhereHas('user', function ($userQuery) use ($value) {
                    $userQuery->where('name', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%");
                });
        });
    }
}

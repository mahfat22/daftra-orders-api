<?php

namespace App\Http\Filters;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Carbon\Carbon;

class PaymentFilter extends BaseFilter
{
    public function filters(): array
    {
        return [
            'status' => $this->getFilterValue('status'),
            'method' => $this->getFilterValue('method'),
            'order_id' => $this->getFilterValue('order_id'),
            'amount_min' => $this->getFilterValue('amount_min'),
            'amount_max' => $this->getFilterValue('amount_max'),
            'date_from' => $this->getFilterValue('date_from'),
            'date_to' => $this->getFilterValue('date_to'),
            'payment_id' => $this->getFilterValue('payment_id'),
        ];
    }

    protected function status($value): void
    {
        if ($status = PaymentStatus::tryFrom($value)) {
            $this->builder->where('status', $status);
        }
    }

    protected function method($value): void
    {
        if ($method = PaymentMethod::tryFrom($value)) {
            $this->builder->where('method', $method);
        }
    }

    protected function order_id($value): void
    {
        $this->builder->where('order_id', $value);
    }

    protected function amount_min($value): void
    {
        if (is_numeric($value)) {
            $this->builder->where('amount', '>=', $value);
        }
    }

    protected function amount_max($value): void
    {
        if (is_numeric($value)) {
            $this->builder->where('amount', '<=', $value);
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

    protected function payment_id($value): void
    {
        $this->builder->where('payment_id', 'like', "%{$value}%");
    }
}

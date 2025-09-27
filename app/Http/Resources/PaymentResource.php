<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_id' => $this->payment_id,
            'order_id' => $this->order_id,
            'status' => $this->status->value,
            'status_label' => ucfirst($this->status->value),
            'method' => $this->method->value,
            'method_label' => ucfirst(str_replace('_', ' ', $this->method->value)),
            'amount' => $this->amount,
            'payload' => $this->when($request->user()?->can('view-payment-details', $this), $this->payload),
            'order' => new OrderResource($this->whenLoaded('order')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}

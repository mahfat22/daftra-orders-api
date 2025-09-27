<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'status' => $this->status->value,
            'status_label' => ucfirst($this->status->value),
            'total' => $this->total,
            'meta' => $this->meta,
            'user' => new UserResource($this->whenLoaded('user')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'items_count' => $this->items_count ?? $this->items?->count(),
            'payments_count' => $this->payments_count ?? $this->payments?->count(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}

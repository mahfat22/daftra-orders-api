<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_can_be_created(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->forUser($user)->create([
            'total' => 99.99,
            'status' => OrderStatus::PENDING,
        ]);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($user->id, $order->user_id);
        $this->assertEquals('99.99', $order->total);
        $this->assertEquals(OrderStatus::PENDING, $order->status);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_order_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->forUser($user)->create();

        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals($user->id, $order->user->id);
    }

    public function test_order_has_items_relationship(): void
    {
        $order = Order::factory()->create();
        $item = OrderItem::factory()->forOrder($order)->create();

        $this->assertInstanceOf(Collection::class, $order->items);
        $this->assertTrue($order->items->contains($item));
        $this->assertEquals(1, $order->items->count());
    }

    public function test_order_has_payments_relationship(): void
    {
        $order = Order::factory()->create();
        $payment = Payment::factory()->forOrder($order)->create();

        $this->assertInstanceOf(Collection::class, $order->payments);
        $this->assertTrue($order->payments->contains($payment));
        $this->assertEquals(1, $order->payments->count());
    }

    public function test_order_status_enum_casting(): void
    {
        $order = Order::factory()->pending()->create();

        $this->assertInstanceOf(OrderStatus::class, $order->status);
        $this->assertEquals(OrderStatus::PENDING, $order->status);
        $this->assertEquals('pending', $order->status->value);
    }

    public function test_order_total_decimal_casting(): void
    {
        $order = Order::factory()->withTotal(123.456)->create();

        $this->assertEquals('123.46', $order->total);
        $this->assertIsString($order->total);
    }

    public function test_order_meta_array_casting(): void
    {
        $meta = [
            'currency' => 'USD',
            'notes' => 'Test order notes',
            'source' => 'api',
        ];

        $order = Order::factory()->create(['meta' => $meta]);

        $this->assertIsArray($order->meta);
        $this->assertEquals($meta, $order->meta);
    }

    public function test_order_fillable_attributes(): void
    {
        $order = new Order;
        $fillable = $order->getFillable();

        $expectedFillable = ['user_id', 'status', 'total', 'meta'];

        foreach ($expectedFillable as $attribute) {
            $this->assertContains($attribute, $fillable);
        }
    }

    public function test_order_factory_states(): void
    {
        $pending = Order::factory()->pending()->create();
        $this->assertEquals(OrderStatus::PENDING, $pending->status);

        $confirmed = Order::factory()->confirmed()->create();
        $this->assertEquals(OrderStatus::CONFIRMED, $confirmed->status);

        $cancelled = Order::factory()->cancelled()->create();
        $this->assertEquals(OrderStatus::CANCELLED, $cancelled->status);
    }

    public function test_order_can_have_multiple_items(): void
    {
        $order = Order::factory()->create();
        $items = OrderItem::factory()->count(3)->forOrder($order)->create();

        $order->refresh();

        $this->assertEquals(3, $order->items->count());

        foreach ($items as $item) {
            $this->assertTrue($order->items->contains($item));
            $this->assertEquals($order->id, $item->order_id);
        }
    }

    public function test_order_can_have_multiple_payments(): void
    {
        $order = Order::factory()->create();

        $failedPayment = Payment::factory()->failed()->forOrder($order)->create([
            'amount' => '50.00',
        ]);
        $successfulPayment = Payment::factory()->successful()->forOrder($order)->create([
            'amount' => '30.00',
        ]);

        $order->refresh();

        $this->assertEquals(2, $order->payments->count());
        $this->assertTrue($order->payments->contains($failedPayment));
        $this->assertTrue($order->payments->contains($successfulPayment));
    }

    public function test_order_total_calculation_with_items(): void
    {
        $order = Order::factory()->create(['total' => 0]);

        OrderItem::factory()->forOrder($order)->withPrice(10.00, 2)->create();
        OrderItem::factory()->forOrder($order)->withPrice(15.50, 1)->create();

        $calculatedTotal = $order->items->sum('total_price');

        $this->assertEquals('35.50', number_format($calculatedTotal, 2));
    }

    public function test_order_timestamps(): void
    {
        $order = Order::factory()->create();

        $this->assertNotNull($order->created_at);
        $this->assertNotNull($order->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $order->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $order->updated_at);
    }
}

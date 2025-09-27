<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderItemModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_item_can_be_created(): void
    {
        $orderItem = OrderItem::factory()->create();

        $this->assertInstanceOf(OrderItem::class, $orderItem);
        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'product_name' => $orderItem->product_name,
        ]);
    }

    public function test_order_item_belongs_to_order(): void
    {
        $order = Order::factory()->create();
        $orderItem = OrderItem::factory()->forOrder($order)->create();

        $this->assertInstanceOf(Order::class, $orderItem->order);
        $this->assertEquals($order->id, $orderItem->order->id);
        $this->assertEquals($order->id, $orderItem->order_id);
    }

    public function test_order_item_quantity_casting(): void
    {
        $orderItem = OrderItem::factory()->create([
            'quantity' => '5',
        ]);

        $this->assertIsInt($orderItem->quantity);
        $this->assertEquals(5, $orderItem->quantity);
    }

    public function test_order_item_unit_price_decimal_casting(): void
    {
        $orderItem = OrderItem::factory()->create([
            'unit_price' => '99.99',
        ]);

        $this->assertIsString($orderItem->unit_price);
        $this->assertEquals('99.99', $orderItem->unit_price);

        $orderItem2 = OrderItem::factory()->create([
            'unit_price' => '99.999',
        ]);

        $this->assertEquals('100.00', $orderItem2->unit_price);
    }

    public function test_order_item_total_price_decimal_casting(): void
    {
        $orderItem = OrderItem::factory()->create([
            'total_price' => '199.99',
        ]);

        $this->assertIsString($orderItem->total_price);
        $this->assertEquals('199.99', $orderItem->total_price);
    }

    public function test_order_item_fillable_attributes(): void
    {
        $orderItem = new OrderItem;
        $fillable = $orderItem->getFillable();

        $expectedFillable = ['order_id', 'product_name', 'quantity', 'unit_price', 'total_price'];

        foreach ($expectedFillable as $attribute) {
            $this->assertContains($attribute, $fillable);
        }
    }

    public function test_order_item_factory_states(): void
    {
        $expensiveItem = OrderItem::factory()->expensive()->create();
        $this->assertGreaterThanOrEqual(100, (float) $expensiveItem->unit_price);

        $bulkItem = OrderItem::factory()->bulk()->create();
        $this->assertGreaterThanOrEqual(10, $bulkItem->quantity);
    }

    public function test_order_item_can_calculate_total_from_unit_price_and_quantity(): void
    {
        $orderItem = OrderItem::factory()->make([
            'unit_price' => '25.50',
            'quantity' => 3,
        ]);

        $expectedTotal = 25.50 * 3;

        $orderItem->total_price = $expectedTotal;
        $orderItem->save();

        $this->assertEquals('76.50', $orderItem->total_price);
    }

    public function test_order_item_timestamps(): void
    {
        $orderItem = OrderItem::factory()->create();

        $this->assertNotNull($orderItem->created_at);
        $this->assertNotNull($orderItem->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $orderItem->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $orderItem->updated_at);
    }

    public function test_order_item_can_be_updated(): void
    {
        $orderItem = OrderItem::factory()->create([
            'product_name' => 'Original Product',
        ]);

        $orderItem->update([
            'product_name' => 'Updated Product',
        ]);

        $this->assertEquals('Updated Product', $orderItem->product_name);
        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'product_name' => 'Updated Product',
        ]);
    }

    public function test_order_item_can_be_deleted(): void
    {
        $orderItem = OrderItem::factory()->create();
        $itemId = $orderItem->id;

        $orderItem->delete();

        $this->assertDatabaseMissing('order_items', [
            'id' => $itemId,
        ]);
    }

    public function test_order_item_relationship_cascade_behavior(): void
    {
        $order = Order::factory()->create();
        $orderItem = OrderItem::factory()->forOrder($order)->create();

        $this->assertTrue($order->items->contains($orderItem));

        $orderItem->delete();
        $order->refresh();

        $this->assertFalse($order->items->contains($orderItem));
        $this->assertEquals(0, $order->items->count());
    }

    public function test_order_item_with_zero_quantity(): void
    {
        $orderItem = OrderItem::factory()->create([
            'quantity' => 0,
            'unit_price' => '10.00',
            'total_price' => '0.00',
        ]);

        $this->assertEquals(0, $orderItem->quantity);
        $this->assertEquals('0.00', $orderItem->total_price);
    }

    public function test_order_item_with_high_precision_prices(): void
    {
        $orderItem = OrderItem::factory()->create([
            'unit_price' => '10.999',
            'total_price' => '21.999',
        ]);

        $this->assertEquals('11.00', $orderItem->unit_price);
        $this->assertEquals('22.00', $orderItem->total_price);
    }
}

<?php

namespace Tests\Unit;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_can_be_created(): void
    {
        $payment = Payment::factory()->create();

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'payment_id' => $payment->payment_id,
        ]);
    }

    public function test_payment_belongs_to_order(): void
    {
        $order = Order::factory()->create();
        $payment = Payment::factory()->forOrder($order)->create();

        $this->assertInstanceOf(Order::class, $payment->order);
        $this->assertEquals($order->id, $payment->order->id);
        $this->assertEquals($order->id, $payment->order_id);
    }

    public function test_payment_status_enum_casting(): void
    {
        $payment = Payment::factory()->create([
            'status' => PaymentStatus::PENDING,
        ]);

        $this->assertInstanceOf(PaymentStatus::class, $payment->status);
        $this->assertEquals(PaymentStatus::PENDING, $payment->status);
        $this->assertEquals('pending', $payment->status->value);
    }

    public function test_payment_method_enum_casting(): void
    {
        $payment = Payment::factory()->create([
            'method' => PaymentMethod::CREDIT_CARD,
        ]);

        $this->assertInstanceOf(PaymentMethod::class, $payment->method);
        $this->assertEquals(PaymentMethod::CREDIT_CARD, $payment->method);
        $this->assertEquals('credit_card', $payment->method->value);
    }

    public function test_payment_amount_decimal_casting(): void
    {
        $payment = Payment::factory()->create([
            'amount' => '199.99',
        ]);

        $this->assertIsString($payment->amount);
        $this->assertEquals('199.99', $payment->amount);

        $payment2 = Payment::factory()->create([
            'amount' => '199.999',
        ]);

        $this->assertEquals('200.00', $payment2->amount);
    }

    public function test_payment_payload_array_casting(): void
    {
        $payloadData = [
            'transaction_id' => 'txn_123456',
            'gateway_response' => 'Success',
            'metadata' => ['ip' => '127.0.0.1'],
        ];

        $payment = Payment::factory()->create([
            'payload' => $payloadData,
        ]);

        $this->assertIsArray($payment->payload);
        $this->assertEquals($payloadData, $payment->payload);
        $this->assertEquals('txn_123456', $payment->payload['transaction_id']);
    }

    public function test_payment_fillable_attributes(): void
    {
        $payment = new Payment;
        $fillable = $payment->getFillable();

        $expectedFillable = ['payment_id', 'order_id', 'status', 'method', 'amount', 'payload'];

        foreach ($expectedFillable as $attribute) {
            $this->assertContains($attribute, $fillable);
        }
    }

    public function test_payment_is_pending_method(): void
    {
        $pendingPayment = Payment::factory()->pending()->create();
        $successfulPayment = Payment::factory()->successful()->create();

        $this->assertTrue($pendingPayment->isPending());
        $this->assertFalse($successfulPayment->isPending());
    }

    public function test_payment_is_successful_method(): void
    {
        $pendingPayment = Payment::factory()->pending()->create();
        $successfulPayment = Payment::factory()->successful()->create();

        $this->assertFalse($pendingPayment->isSuccessful());
        $this->assertTrue($successfulPayment->isSuccessful());
    }

    public function test_payment_is_failed_method(): void
    {
        $pendingPayment = Payment::factory()->pending()->create();
        $failedPayment = Payment::factory()->failed()->create();

        $this->assertFalse($pendingPayment->isFailed());
        $this->assertTrue($failedPayment->isFailed());
    }

    public function test_payment_factory_states(): void
    {
        $pendingPayment = Payment::factory()->pending()->create();
        $this->assertEquals(PaymentStatus::PENDING, $pendingPayment->status);

        $successfulPayment = Payment::factory()->successful()->create();
        $this->assertEquals(PaymentStatus::SUCCESSFUL, $successfulPayment->status);

        $failedPayment = Payment::factory()->failed()->create();
        $this->assertEquals(PaymentStatus::FAILED, $failedPayment->status);

        $creditCardPayment = Payment::factory()->creditCard()->create();
        $this->assertEquals(PaymentMethod::CREDIT_CARD, $creditCardPayment->method);

        $paypalPayment = Payment::factory()->paypal()->create();
        $this->assertEquals(PaymentMethod::PAYPAL, $paypalPayment->method);

        $highAmountPayment = Payment::factory()->highAmount()->create();
        $this->assertGreaterThanOrEqual(100, (float) $highAmountPayment->amount);
    }

    public function test_payment_timestamps(): void
    {
        $payment = Payment::factory()->create();

        $this->assertNotNull($payment->created_at);
        $this->assertNotNull($payment->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $payment->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $payment->updated_at);
    }

    public function test_payment_can_be_updated(): void
    {
        $payment = Payment::factory()->pending()->create();

        $payment->update([
            'status' => PaymentStatus::SUCCESSFUL,
        ]);

        $this->assertEquals(PaymentStatus::SUCCESSFUL, $payment->status);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => PaymentStatus::SUCCESSFUL->value,
        ]);
    }

    public function test_payment_unique_payment_id(): void
    {
        $payment1 = Payment::factory()->create(['payment_id' => 'unique_123']);
        $payment2 = Payment::factory()->create(['payment_id' => 'unique_456']);

        $this->assertNotEquals($payment1->payment_id, $payment2->payment_id);
        $this->assertEquals('unique_123', $payment1->payment_id);
        $this->assertEquals('unique_456', $payment2->payment_id);
    }

    public function test_payment_can_be_deleted(): void
    {
        $payment = Payment::factory()->create();
        $paymentId = $payment->id;

        $payment->delete();

        $this->assertDatabaseMissing('payments', [
            'id' => $paymentId,
        ]);
    }

    public function test_payment_relationship_with_order_cascade_behavior(): void
    {
        $order = Order::factory()->create();
        $payment = Payment::factory()->forOrder($order)->create();

        $this->assertTrue($order->payments->contains($payment));

        $payment->delete();
        $order->refresh();

        $this->assertFalse($order->payments->contains($payment));
        $this->assertEquals(0, $order->payments->count());
    }

    public function test_payment_with_zero_amount(): void
    {
        $payment = Payment::factory()->create([
            'amount' => '0.00',
        ]);

        $this->assertEquals('0.00', $payment->amount);
    }

    public function test_payment_with_large_amount(): void
    {
        $payment = Payment::factory()->create([
            'amount' => '999999.99',
        ]);

        $this->assertEquals('999999.99', $payment->amount);
    }

    public function test_payment_payload_with_complex_data(): void
    {
        $complexPayload = [
            'gateway' => 'stripe',
            'transaction_id' => 'txn_1ABC123456',
            'response' => [
                'status' => 'succeeded',
                'amount_received' => 10000,
                'currency' => 'usd',
                'metadata' => [
                    'order_id' => '12345',
                    'customer_id' => '67890',
                ],
            ],
            'fees' => [
                'stripe_fee' => 59,
                'application_fee' => 0,
            ],
        ];

        $payment = Payment::factory()->create([
            'payload' => $complexPayload,
        ]);

        $this->assertIsArray($payment->payload);
        $this->assertEquals('stripe', $payment->payload['gateway']);
        $this->assertEquals('succeeded', $payment->payload['response']['status']);
        $this->assertEquals(59, $payment->payload['fees']['stripe_fee']);
    }
}

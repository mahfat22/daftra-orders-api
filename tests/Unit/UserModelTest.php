<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertNotNull($user->password);
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    public function test_user_has_orders_relationship(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->forUser($user)->create();

        $this->assertInstanceOf(Collection::class, $user->orders);
        $this->assertTrue($user->orders->contains($order));
        $this->assertEquals(1, $user->orders->count());
    }

    public function test_user_can_have_multiple_orders(): void
    {
        $user = User::factory()->create();
        $orders = Order::factory()->count(3)->forUser($user)->create();

        $user->refresh();

        $this->assertEquals(3, $user->orders->count());

        foreach ($orders as $order) {
            $this->assertTrue($user->orders->contains($order));
            $this->assertEquals($user->id, $order->user_id);
        }
    }

    public function test_user_fillable_attributes(): void
    {
        $user = new User;
        $fillable = $user->getFillable();

        $expectedFillable = ['name', 'email', 'password'];

        foreach ($expectedFillable as $attribute) {
            $this->assertContains($attribute, $fillable);
        }
    }

    public function test_user_hidden_attributes(): void
    {
        $user = User::factory()->create();
        $hiddenAttributes = $user->getHidden();

        $this->assertContains('password', $hiddenAttributes);
        $this->assertContains('remember_token', $hiddenAttributes);
    }

    public function test_user_password_is_hashed(): void
    {
        $user = User::factory()->create([
            'password' => 'plaintext_password',
        ]);

        $this->assertNotEquals('plaintext_password', $user->password);
        $this->assertTrue(Hash::check('plaintext_password', $user->password));
    }

    public function test_user_email_verification(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->assertFalse($user->hasVerifiedEmail());

        $user->markEmailAsVerified();

        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_user_factory_states(): void
    {
        $unverified = User::factory()->unverified()->create();
        $this->assertNull($unverified->email_verified_at);

        $verified = User::factory()->create();
        $this->assertNotNull($verified->email_verified_at);
    }

    public function test_user_can_be_soft_deleted(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->deleted_at);

        $user->delete();

        $this->assertNotNull($user->deleted_at);
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => Roles::ADMIN]);
    }

    public function test_only_admin_can_manage_users(): void
    {
        foreach ([Roles::MANAGER, Roles::SEO, Roles::CUSTOMER] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get('/admin/users')->assertForbidden();
        }

        $this->actingAs($this->admin())->get('/admin/users')->assertOk();
    }

    public function test_list_contains_only_staff(): void
    {
        $manager = User::factory()->create(['role' => Roles::MANAGER]);
        $customer = User::factory()->create(['role' => Roles::CUSTOMER]);

        $this->actingAs($this->admin())
            ->get('/admin/users')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('users/IndexUsers')
                ->has('users.data', 2)
                ->where('users.data', fn ($users) => collect($users)->pluck('email')->contains($manager->email)
                    && ! collect($users)->pluck('email')->contains($customer->email))
            );
    }

    public function test_admin_can_create_user_and_verification_email_is_sent(): void
    {
        Notification::fake();

        $this->actingAs($this->admin())
            ->post('/admin/users', [
                'name' => 'Пётр Петров',
                'email' => 'Manager@Furniture.com',
                'role' => Roles::MANAGER->value,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/users');

        $user = User::where('email', 'manager@furniture.com')->firstOrFail();

        $this->assertSame(Roles::MANAGER, $user->role);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue(Hash::check('password123', $user->password));

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_customer_role_cannot_be_assigned(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/users', [
                'name' => 'Test',
                'email' => 'test@furniture.com',
                'role' => Roles::CUSTOMER->value,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertSessionHasErrors('role');
    }

    public function test_email_of_soft_deleted_user_cannot_be_reused(): void
    {
        $deleted = User::factory()->create(['role' => Roles::SEO]);
        $deleted->delete();

        $this->actingAs($this->admin())
            ->post('/admin/users', [
                'name' => 'Test',
                'email' => $deleted->email,
                'role' => Roles::SEO->value,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_admin_can_update_user_without_changing_password(): void
    {
        Notification::fake();

        $user = User::factory()->create(['role' => Roles::SEO]);
        $oldPassword = $user->password;

        $this->actingAs($this->admin())
            ->put("/admin/users/{$user->id}", [
                'name' => 'New Name',
                'email' => $user->email,
                'role' => Roles::MANAGER->value,
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/users');

        $user->refresh();

        $this->assertSame('New Name', $user->name);
        $this->assertSame(Roles::MANAGER, $user->role);
        $this->assertSame($oldPassword, $user->password);
        $this->assertNotNull($user->email_verified_at);

        Notification::assertNothingSent();
    }

    public function test_changing_email_resets_verification_and_sends_email(): void
    {
        Notification::fake();

        $user = User::factory()->create(['role' => Roles::SEO]);

        $this->actingAs($this->admin())
            ->put("/admin/users/{$user->id}", [
                'name' => $user->name,
                'email' => 'new@furniture.com',
                'role' => Roles::SEO->value,
            ])
            ->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertSame('new@furniture.com', $user->email);
        $this->assertNull($user->email_verified_at);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_admin_cannot_change_own_role(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put("/admin/users/{$admin->id}", [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => Roles::SEO->value,
            ])
            ->assertSessionHasErrors('role');

        $this->assertSame(Roles::ADMIN, $admin->refresh()->role);
    }

    public function test_admin_can_soft_delete_user(): void
    {
        $user = User::factory()->create(['role' => Roles::MANAGER]);

        $this->actingAs($this->admin())
            ->delete("/admin/users/{$user->id}")
            ->assertRedirect('/admin/users');

        $this->assertSoftDeleted($user);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from('/admin/users')
            ->delete("/admin/users/{$admin->id}")
            ->assertRedirect('/admin/users')
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($admin);
    }

    public function test_customers_are_not_editable_here(): void
    {
        $customer = User::factory()->create(['role' => Roles::CUSTOMER]);

        $this->actingAs($this->admin())
            ->get("/admin/users/{$customer->id}/edit")
            ->assertNotFound();
    }

    public function test_verification_email_can_be_resent(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create(['role' => Roles::SEO]);

        $this->actingAs($this->admin())
            ->post("/admin/users/{$user->id}/verification-notification")
            ->assertSessionHas('success');

        Notification::assertSentTo($user, VerifyEmail::class);
    }
}

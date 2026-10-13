<?php

namespace Tests\Feature\Admin;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/languages')->assertRedirect(route('login'));
    }

    public function test_non_staff_cannot_access_admin(): void
    {
        $customer = User::factory()->create(['role' => Roles::CUSTOMER]);
        $withoutRole = User::factory()->create(['role' => null]);

        foreach ([$customer, $withoutRole] as $user) {
            $this->actingAs($user)->get('/admin/languages')->assertForbidden();
            $this->actingAs($user)->get('/admin/profile')->assertForbidden();
            $this->actingAs($user)->get('/dashboard')->assertForbidden();
        }
    }

    public function test_staff_can_access_admin(): void
    {
        foreach (Roles::employeeRoles() as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get('/admin/languages')->assertOk();
            $this->actingAs($user)->get('/admin/profile')->assertOk();
        }
    }

    public function test_non_staff_can_still_log_out(): void
    {
        $customer = User::factory()->create(['role' => Roles::CUSTOMER]);

        $this->actingAs($customer)->post('/logout');

        $this->assertGuest();
    }
}

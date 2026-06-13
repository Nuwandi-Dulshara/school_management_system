<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_admin_can_access_user_management(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($superAdmin)
            ->get(route('super_admin.users.index'))
            ->assertOk();

        $this->actingAs($student)
            ->get(route('super_admin.users.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_create_update_and_search_for_a_user(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superAdmin)
            ->post(route('super_admin.users.store'), [
                'first_name' => 'Jane',
                'last_name' => 'Teacher',
                'email' => 'jane@school.test',
                'username' => 'janeteacher',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'teacher',
                'status' => 'active',
            ])
            ->assertRedirect(route('super_admin.users.index'));

        $user = User::where('username', 'janeteacher')->firstOrFail();

        $this->actingAs($superAdmin)
            ->put(route('super_admin.users.update', $user), [
                'first_name' => 'Jane',
                'last_name' => 'Updated',
                'email' => 'jane@school.test',
                'username' => 'janeteacher',
                'role' => 'admin',
                'status' => 'active',
            ])
            ->assertRedirect(route('super_admin.users.show', $user));

        $this->actingAs($superAdmin)
            ->get(route('super_admin.users.index', ['search' => 'janeteacher', 'role' => 'admin']))
            ->assertOk()
            ->assertSee('Jane Updated');
    }

    public function test_super_admin_can_toggle_status_and_reset_password(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $user = User::factory()->create();

        $this->actingAs($superAdmin)
            ->patch(route('super_admin.users.status', $user))
            ->assertRedirect();

        $this->assertSame('inactive', $user->fresh()->status);

        $this->actingAs($superAdmin)
            ->put(route('super_admin.users.password', $user), [
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_super_admin_cannot_deactivate_or_delete_own_account(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superAdmin)
            ->patch(route('super_admin.users.status', $superAdmin))
            ->assertSessionHas('error');

        $this->actingAs($superAdmin)
            ->delete(route('super_admin.users.destroy', $superAdmin))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $superAdmin->id,
            'status' => 'active',
        ]);
    }
}

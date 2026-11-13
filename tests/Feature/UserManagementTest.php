<?php

use App\Enums\UserRole;
use App\Models\User;

test('user biasa mendapat 403 di halaman kelola user', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/users')
        ->assertForbidden();
});

test('menu Kelola User hanya tampil untuk admin', function () {
    $this->actingAs(User::factory()->admin()->create())->get('/dashboard')->assertSee('Kelola User');
    $this->actingAs(User::factory()->create())->get('/dashboard')->assertDontSee('Kelola User');
});

test('admin dapat melihat daftar user', function () {
    User::factory()->create(['name' => 'Anggota Satu']);

    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin/users')
        ->assertOk()
        ->assertSee('Anggota Satu');
});

test('admin dapat menambah user dengan role admin', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->post('/admin/users', [
            'name' => 'Petugas Baru',
            'email' => 'petugas@example.com',
            'role' => 'admin',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success', 'User berhasil ditambahkan.');

    expect(User::where('email', 'petugas@example.com')->first()->role)->toBe(UserRole::Admin);
});

test('role selain admin dan user ditolak', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->post('/admin/users', [
            'name' => 'X',
            'email' => 'x@example.com',
            'role' => 'superadmin',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertSessionHasErrors('role');
});

test('admin dapat mengubah role user lain tanpa mengganti password', function () {
    $user = User::factory()->create(['password' => 'password']);
    $passwordLama = $user->password;

    $this->actingAs(User::factory()->admin()->create())
        ->put("/admin/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'admin',
            'password' => '',
            'password_confirmation' => '',
        ])
        ->assertRedirect(route('admin.users.index'));

    expect($user->fresh())->role->toBe(UserRole::Admin)->password->toBe($passwordLama);
});

test('admin tidak dapat menurunkan role akunnya sendiri', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->put("/admin/users/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'user',
        ])
        ->assertSessionHasErrors(['role' => 'Anda tidak dapat menurunkan role akun Anda sendiri.']);

    expect($admin->fresh()->role)->toBe(UserRole::Admin);
});

test('admin dapat menghapus user lain', function () {
    $user = User::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->delete("/admin/users/{$user->id}")
        ->assertSessionHas('success', 'User berhasil dihapus.');

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('admin tidak dapat menghapus akunnya sendiri', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->delete("/admin/users/{$admin->id}")
        ->assertSessionHas('error', 'Anda tidak dapat menghapus akun Anda sendiri.');

    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

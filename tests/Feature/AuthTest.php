<?php

use App\Enums\UserRole;
use App\Models\User;

test('tamu diarahkan ke halaman login saat membuka dashboard', function () {
    $this->get('/dashboard')->assertRedirect(route('login'));
});

test('halaman login dan register dapat dibuka tamu', function () {
    $this->get('/login')->assertOk()->assertSee('Masuk');
    $this->get('/register')->assertOk()->assertSee('Buat Akun');
});

test('user yang sudah login dialihkan ke dashboard saat membuka login', function () {
    $this->actingAs(User::factory()->create())
        ->get('/login')
        ->assertRedirect(route('dashboard'));
});

test('register membuat akun baru dengan role user', function () {
    $this->post('/register', [
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'admin',
    ])->assertRedirect(route('dashboard'));

    $user = User::where('email', 'budi@example.com')->first();

    expect($user->role)->toBe(UserRole::User);
    $this->assertAuthenticatedAs($user);
});

test('register gagal jika data tidak valid', function () {
    User::factory()->create(['email' => 'ada@example.com']);

    $this->post('/register', [
        'name' => '',
        'email' => 'ada@example.com',
        'password' => 'pendek',
        'password_confirmation' => 'beda',
    ])->assertSessionHasErrors(['name', 'email', 'password']);

    $this->assertGuest();
});

test('login berhasil dengan email dan password yang benar', function () {
    $user = User::factory()->create(['password' => 'password']);

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('login gagal dengan password yang salah', function () {
    $user = User::factory()->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'salah'])
        ->assertSessionHasErrors(['email' => 'Email atau password salah.']);

    $this->assertGuest();
});

test('logout mengeluarkan user', function () {
    $this->actingAs(User::factory()->create())
        ->post('/logout')
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

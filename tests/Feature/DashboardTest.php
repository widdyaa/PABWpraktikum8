<?php

use App\Models\User;

test('dashboard admin menampilkan total user', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Total Buku')
        ->assertSee('Total User');
});

test('dashboard user tidak menampilkan total user', function () {
    $this->actingAs(User::factory()->create())
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Total Buku')
        ->assertDontSee('Total User');
});

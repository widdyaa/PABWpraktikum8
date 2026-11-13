<?php

use App\Models\User;
use Modules\Buku\Models\Buku;

function dataBuku(array $override = []): array
{
    return array_merge([
        'judul' => 'Laskar Pelangi',
        'penulis' => 'Andrea Hirata',
        'penerbit' => 'Bentang Pustaka',
        'tahun_terbit' => 2005,
        'stok' => 5,
    ], $override);
}

test('admin dan user dapat melihat daftar buku', function () {
    Buku::factory()->create(['judul' => 'Bumi Manusia']);

    $this->actingAs(User::factory()->admin()->create())
        ->get('/buku')->assertOk()->assertSee('Bumi Manusia')->assertSee('Tambah Buku');

    $this->actingAs(User::factory()->create())
        ->get('/buku')->assertOk()->assertSee('Bumi Manusia')->assertDontSee('Tambah Buku');
});

test('user mendapat 403 saat membuka halaman tambah dan edit buku', function () {
    $buku = Buku::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user)->get('/buku/create')->assertForbidden();
    $this->actingAs($user)->get("/buku/{$buku->id}/edit")->assertForbidden();
});

test('user tidak dapat menyimpan, mengubah, atau menghapus buku', function () {
    $buku = Buku::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user)->post('/buku', dataBuku())->assertForbidden();
    $this->actingAs($user)->put("/buku/{$buku->id}", dataBuku())->assertForbidden();
    $this->actingAs($user)->delete("/buku/{$buku->id}")->assertForbidden();

    expect(Buku::count())->toBe(1);
});

test('admin dapat menambah buku', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->post('/buku', dataBuku())
        ->assertRedirect(route('buku.index'))
        ->assertSessionHas('success', 'Buku berhasil ditambahkan.');

    $this->assertDatabaseHas('bukus', ['judul' => 'Laskar Pelangi', 'stok' => 5]);
});

test('tambah buku gagal jika data tidak valid', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->post('/buku', dataBuku(['judul' => '', 'tahun_terbit' => 1800, 'stok' => -1]))
        ->assertSessionHasErrors(['judul', 'tahun_terbit', 'stok']);

    expect(Buku::count())->toBe(0);
});

test('pesan error validasi tampil dalam bahasa Indonesia', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->post('/buku', dataBuku(['judul' => '']))
        ->assertSessionHasErrors(['judul' => 'Judul wajib diisi.']);
});

test('admin dapat mengubah buku', function () {
    $buku = Buku::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->put("/buku/{$buku->id}", dataBuku(['judul' => 'Sang Pemimpi', 'stok' => 2]))
        ->assertRedirect(route('buku.index'))
        ->assertSessionHas('success', 'Buku berhasil diperbarui.');

    expect($buku->fresh())->judul->toBe('Sang Pemimpi')->stok->toBe(2);
});

test('admin dapat menghapus buku', function () {
    $buku = Buku::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->delete("/buku/{$buku->id}")
        ->assertRedirect(route('buku.index'))
        ->assertSessionHas('success', 'Buku berhasil dihapus.');

    $this->assertDatabaseMissing('bukus', ['id' => $buku->id]);
});

test('pencarian buku berdasarkan judul atau penulis', function () {
    Buku::factory()->create(['judul' => 'Laskar Pelangi', 'penulis' => 'Andrea Hirata']);
    Buku::factory()->create(['judul' => 'Bumi Manusia', 'penulis' => 'Pramoedya Ananta Toer']);

    $this->actingAs(User::factory()->create())
        ->get('/buku?search=pramoedya')
        ->assertSee('Bumi Manusia')
        ->assertDontSee('Laskar Pelangi');
});

test('daftar buku memakai pagination 10 data per halaman', function () {
    Buku::factory()->count(12)->create();

    $this->actingAs(User::factory()->create())
        ->get('/buku')
        ->assertViewHas('bukus', fn ($bukus) => $bukus->count() === 10 && $bukus->total() === 12);
});

test('buku yang tidak ada menghasilkan 404', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/buku/999/edit')
        ->assertNotFound();
});

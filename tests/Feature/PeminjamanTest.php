<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Peminjaman\Models\Peminjaman;

beforeEach(function () {
    Storage::fake('public');
});

function dataPeminjaman(array $override = []): array
{
    return array_merge([
        'nama_peminjam' => 'Budi Santoso',
        'judul_buku' => 'Laskar Pelangi',
        'tanggal_pinjam' => '2026-11-01',
        'tanggal_kembali' => '2026-11-08',
        'status' => 'Dipinjam',
        'bukti' => UploadedFile::fake()->image('bukti.jpg'),
    ], $override);
}

function ajax($test)
{
    return $test->withHeaders(['Accept' => 'application/json']);
}

test('tamu diarahkan ke login saat membuka halaman peminjaman', function () {
    $this->get('/peminjaman')->assertRedirect(route('login'));
});

test('admin dan user dapat membuka halaman peminjaman', function () {
    $this->actingAs(User::factory()->admin()->create())->get('/peminjaman')->assertOk()->assertSee('Tambah Peminjaman');
    $this->actingAs(User::factory()->create())->get('/peminjaman')->assertOk()->assertSee('Tambah Peminjaman');
});

test('route data mengembalikan JSON beserta url bukti', function () {
    Peminjaman::factory()->create(['nama_peminjam' => 'Siti', 'bukti' => 'bukti_peminjaman/a.jpg']);

    $this->actingAs(User::factory()->create())
        ->getJson('/peminjaman/data')
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.nama_peminjam', 'Siti')
        ->assertJsonPath('0.bukti_url', asset('storage/bukti_peminjaman/a.jpg'));
});

test('user dapat menambah peminjaman beserta bukti', function () {
    ajax($this->actingAs(User::factory()->create()))
        ->post('/peminjaman', dataPeminjaman())
        ->assertOk()
        ->assertJson(['success' => 'Data peminjaman berhasil disimpan.']);

    $peminjaman = Peminjaman::first();

    expect($peminjaman->nama_peminjam)->toBe('Budi Santoso')
        ->and($peminjaman->nama_bukti)->toBe('bukti.jpg');
    Storage::disk('public')->assertExists($peminjaman->bukti);
});

test('validasi gagal mengembalikan 422 dengan pesan per input', function () {
    ajax($this->actingAs(User::factory()->create()))
        ->post('/peminjaman', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['nama_peminjam', 'judul_buku', 'tanggal_pinjam', 'tanggal_kembali', 'status', 'bukti'])
        ->assertJsonPath('errors.bukti.0', 'Bukti peminjaman wajib diunggah.')
        ->assertJsonPath('errors.nama_peminjam.0', 'Nama peminjam wajib diisi.');
});

test('tanggal kembali tidak boleh sebelum tanggal pinjam', function () {
    ajax($this->actingAs(User::factory()->create()))
        ->post('/peminjaman', dataPeminjaman(['tanggal_kembali' => '2026-10-01']))
        ->assertStatus(422)
        ->assertJsonValidationErrors('tanggal_kembali');
});

test('status hanya boleh Dipinjam atau Dikembalikan', function () {
    ajax($this->actingAs(User::factory()->create()))
        ->post('/peminjaman', dataPeminjaman(['status' => 'Hilang']))
        ->assertStatus(422)
        ->assertJsonValidationErrors('status');
});

test('bukti berformat zip ditolak', function () {
    ajax($this->actingAs(User::factory()->create()))
        ->post('/peminjaman', dataPeminjaman(['bukti' => UploadedFile::fake()->create('bukti.zip', 10, 'application/zip')]))
        ->assertStatus(422)
        ->assertJsonPath('errors.bukti.0', 'Bukti harus berformat jpg, jpeg, png, atau pdf.');
});

test('bukti lebih dari 2 MB ditolak', function () {
    ajax($this->actingAs(User::factory()->create()))
        ->post('/peminjaman', dataPeminjaman(['bukti' => UploadedFile::fake()->create('bukti.pdf', 3000, 'application/pdf')]))
        ->assertStatus(422)
        ->assertJsonPath('errors.bukti.0', 'Ukuran bukti maksimal 2 MB.');
});

test('route edit mengembalikan satu data dalam JSON', function () {
    $peminjaman = Peminjaman::factory()->create(['tanggal_pinjam' => '2026-11-01']);

    $this->actingAs(User::factory()->create())
        ->getJson("/peminjaman/{$peminjaman->id}/edit")
        ->assertOk()
        ->assertJsonPath('id', $peminjaman->id)
        ->assertJsonPath('tanggal_pinjam', '2026-11-01');
});

test('update tanpa bukti baru tetap memakai bukti lama', function () {
    $user = User::factory()->create();
    ajax($this->actingAs($user))->post('/peminjaman', dataPeminjaman());
    $peminjaman = Peminjaman::first();
    $buktiLama = $peminjaman->bukti;

    ajax($this->actingAs($user))
        ->post("/peminjaman/{$peminjaman->id}", dataPeminjaman(['_method' => 'PUT', 'status' => 'Dikembalikan', 'bukti' => null]))
        ->assertOk()
        ->assertJson(['success' => 'Data peminjaman berhasil diperbarui.']);

    expect($peminjaman->fresh())->status->toBe('Dikembalikan')->bukti->toBe($buktiLama);
    Storage::disk('public')->assertExists($buktiLama);
});

test('update dengan bukti baru menghapus bukti lama', function () {
    $user = User::factory()->create();
    ajax($this->actingAs($user))->post('/peminjaman', dataPeminjaman());
    $peminjaman = Peminjaman::first();
    $buktiLama = $peminjaman->bukti;

    ajax($this->actingAs($user))
        ->post("/peminjaman/{$peminjaman->id}", dataPeminjaman([
            '_method' => 'PUT',
            'bukti' => UploadedFile::fake()->create('bukti-baru.pdf', 100, 'application/pdf'),
        ]))
        ->assertOk();

    $peminjaman->refresh();

    expect($peminjaman->nama_bukti)->toBe('bukti-baru.pdf');
    Storage::disk('public')->assertMissing($buktiLama);
    Storage::disk('public')->assertExists($peminjaman->bukti);
});

test('hapus peminjaman ikut menghapus bukti dari storage', function () {
    $user = User::factory()->create();
    ajax($this->actingAs($user))->post('/peminjaman', dataPeminjaman());
    $peminjaman = Peminjaman::first();

    ajax($this->actingAs($user))
        ->delete("/peminjaman/{$peminjaman->id}")
        ->assertOk()
        ->assertJson(['success' => 'Data peminjaman berhasil dihapus.']);

    $this->assertDatabaseMissing('peminjaman', ['id' => $peminjaman->id]);
    Storage::disk('public')->assertMissing($peminjaman->bukti);
});

test('peminjaman yang tidak ada menghasilkan 404', function () {
    $this->actingAs(User::factory()->create())
        ->getJson('/peminjaman/999/edit')
        ->assertNotFound();
});

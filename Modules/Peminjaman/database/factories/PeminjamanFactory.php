<?php

namespace Modules\Peminjaman\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Peminjaman\Models\Peminjaman;

class PeminjamanFactory extends Factory
{
    protected $model = Peminjaman::class;

    public function definition(): array
    {
        $tanggalPinjam = fake()->dateTimeBetween('-1 month', 'now');

        return [
            'nama_peminjam' => fake()->name(),
            'judul_buku' => ucwords(fake()->words(3, true)),
            'tanggal_pinjam' => $tanggalPinjam,
            'tanggal_kembali' => (clone $tanggalPinjam)->modify('+7 days'),
            'status' => 'Dipinjam',
        ];
    }
}

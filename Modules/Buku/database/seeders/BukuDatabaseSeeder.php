<?php

namespace Modules\Buku\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Buku\Models\Buku;

class BukuDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $bukus = [
            ['Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, 5],
            ['Sang Pemimpi', 'Andrea Hirata', 'Bentang Pustaka', 2006, 3],
            ['Bumi Manusia', 'Pramoedya Ananta Toer', 'Hasta Mitra', 1980, 2],
            ['Ronggeng Dukuh Paruk', 'Ahmad Tohari', 'Gramedia Pustaka Utama', 1982, 4],
            ['Negeri 5 Menara', 'Ahmad Fuadi', 'Gramedia Pustaka Utama', 2009, 6],
            ['Ayat-Ayat Cinta', 'Habiburrahman El Shirazy', 'Republika', 2004, 3],
            ['Perahu Kertas', 'Dee Lestari', 'Bentang Pustaka', 2009, 4],
            ['Pulang', 'Leila S. Chudori', 'Kepustakaan Populer Gramedia', 2012, 2],
            ['Hujan', 'Tere Liye', 'Gramedia Pustaka Utama', 2016, 7],
            ['Atomic Habits', 'James Clear', 'Avery', 2018, 5],
            ['Clean Code', 'Robert C. Martin', 'Prentice Hall', 2008, 3],
            ['The Pragmatic Programmer', 'Andrew Hunt dan David Thomas', 'Addison-Wesley', 1999, 2],
            ['Refactoring', 'Martin Fowler', 'Addison-Wesley', 1999, 1],
            ['Design Patterns', 'Erich Gamma dkk.', 'Addison-Wesley', 1994, 0],
        ];

        foreach ($bukus as [$judul, $penulis, $penerbit, $tahun, $stok]) {
            Buku::create([
                'judul' => $judul,
                'penulis' => $penulis,
                'penerbit' => $penerbit,
                'tahun_terbit' => $tahun,
                'stok' => $stok,
            ]);
        }
    }
}

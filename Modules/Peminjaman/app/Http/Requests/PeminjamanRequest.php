<?php

namespace Modules\Peminjaman\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PeminjamanRequest extends FormRequest
{
    public function rules(): array
    {
        $peminjaman = $this->route('peminjaman');

        return [
            'nama_peminjam' => ['required', 'string', 'max:255'],
            'judul_buku' => ['required', 'string', 'max:255'],
            'tanggal_pinjam' => ['required', 'date'],
            'tanggal_kembali' => ['required', 'date', 'after_or_equal:tanggal_pinjam'],
            'status' => ['required', Rule::in(['Dipinjam', 'Dikembalikan'])],
            'bukti' => [$peminjaman ? 'nullable' : 'required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'bukti.required' => 'Bukti peminjaman wajib diunggah.',
            'bukti.mimes' => 'Bukti harus berformat jpg, jpeg, png, atau pdf.',
            'bukti.max' => 'Ukuran bukti maksimal 2 MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_peminjam' => 'nama peminjam',
            'judul_buku' => 'judul buku',
            'tanggal_pinjam' => 'tanggal pinjam',
            'tanggal_kembali' => 'tanggal kembali',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}

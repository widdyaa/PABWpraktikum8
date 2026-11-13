<?php

return [
    'required' => ':Attribute wajib diisi.',
    'string' => ':Attribute harus berupa teks.',
    'email' => ':Attribute harus berupa alamat email yang valid.',
    'unique' => ':Attribute sudah digunakan.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'integer' => ':Attribute harus berupa angka bulat.',
    'date' => ':Attribute harus berupa tanggal yang valid.',
    'after_or_equal' => ':Attribute harus berupa tanggal setelah atau sama dengan :date.',
    'in' => ':Attribute yang dipilih tidak valid.',
    'enum' => ':Attribute yang dipilih tidak valid.',
    'file' => ':Attribute harus berupa berkas.',
    'mimes' => ':Attribute harus berupa berkas berjenis: :values.',
    'min' => [
        'numeric' => ':Attribute minimal :min.',
        'string' => ':Attribute minimal :min karakter.',
    ],
    'max' => [
        'numeric' => ':Attribute maksimal :max.',
        'string' => ':Attribute maksimal :max karakter.',
        'file' => ':Attribute maksimal berukuran :max kilobyte.',
    ],

    'attributes' => [
        'name' => 'nama',
        'email' => 'email',
        'password' => 'password',
        'role' => 'role',
    ],
];

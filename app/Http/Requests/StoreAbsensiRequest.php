<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAbsensiRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'status' => 'required|in:Hadir,Izin,Cuti,Sakit,Terlambat,Tugas Luar',
            'alasan' => 'nullable|string|max:500|required_if:status,Terlambat',
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Status tidak dikenal.',
            'alasan.required_if' => 'Alasan wajib diisi untuk status Terlambat.',
        ];
    }
}
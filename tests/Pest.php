<?php

use Tests\TestCase;
use App\Models\User;
use App\Models\Absensi;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/*
|--------------------------------------------------------------------------
| Global Test Setup
|--------------------------------------------------------------------------
*/

uses(TestCase::class, DatabaseTransactions::class)
    ->beforeEach(function () {
        $this->withoutMiddleware();
    })
    ->in('Feature');

uses(TestCase::class, DatabaseTransactions::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Custom Helpers (White Box Setup)
|--------------------------------------------------------------------------
*/

/**
 * Membuat user dengan atribut spesifik untuk pengetesan logika internal.
 */
function createTestUser($level = 'anggota', $bidang = 'IT', $role = 'user') {
    return User::create([
        'nama' => 'Test User '.uniqid(),
        'username' => 'test_'.uniqid(),
        'password' => bcrypt('password'),
        'level' => $level,
        'bidang' => $bidang,
        'role' => $role,
        'is_active' => true,
        'point' => 10
    ]);
}

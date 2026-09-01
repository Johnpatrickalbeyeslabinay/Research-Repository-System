<?php

namespace App\Imports;

use App\Mail\repositoryMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Generate password
        $generatedPassword = Str::password(
            length: 8,
            letters: true,
            numbers: true,
            symbols: false
        );

        // Create student account
        $user = User::create([
            'user_id' => $row['user_id'],
            'email' => $row['email'],
            'role' => 'student',
            'name' => $row['name'],
            'year_level' => $row['year_level'],
            'password' => Hash::make($generatedPassword),
        ]);

        // Send account credentials
        Mail::to($user->email)->send(
            new repositoryMail(
                $user->name,
                $user->user_id,
                $user->email,
                $generatedPassword,
                $user->role
            )
        );

        return $user;
    }
}

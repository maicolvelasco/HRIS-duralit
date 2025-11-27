<?php

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

Route::post('/login', function (Request $request) {
    $request->validate([
        'codigo'   => 'required|string',
        'password' => 'required|string',
    ]);

    $user = User::where('codigo', $request->codigo)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'codigo' => ['Credenciales incorrectas'],
        ]);
    }

    auth()->login($user);

    return response()->json([
        'message' => 'Login exitoso',
        'user' => $user->only('id', 'nombre', 'apellido', 'codigo'),
    ]);
});
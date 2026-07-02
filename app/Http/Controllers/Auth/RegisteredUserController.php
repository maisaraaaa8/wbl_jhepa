<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;


class RegisteredUserController extends Controller
{

    public function create()
    {
        return view('auth.register');
    }



    public function store(Request $request)
    {

        $request->validate([

            'email' => 'required|email',

            'password' => 'required|confirmed|min:8',

        ]);



        /*
        |--------------------------------------------------------------------------
        | 1. CREATE USER IN SUPABASE AUTH
        |--------------------------------------------------------------------------
        */


        $response = Http::withHeaders([

            'apikey' => config('supabase.key'),

            'Authorization' =>
                'Bearer '.config('supabase.key'),

            'Content-Type' => 'application/json'

        ])
        ->post(

            config('supabase.url').'/auth/v1/signup',

            [

                'email' => $request->email,

                'password' => $request->password,

            ]

        );




        // Check Supabase response

        if ($response->failed()) {


            $error = $response->json();


            return back()
                ->withInput()
                ->withErrors([

                    'email' =>
                    $error['msg']
                    ?? 'Registration failed'

                ]);

        }




        $supabaseUser = $response->json();


if (!isset($supabaseUser['user'])) {

    dd([
        'supabase_response' => $supabaseUser
    ]);

}


$uuid = $supabaseUser['user']['id'];




        /*
        |--------------------------------------------------------------------------
        | 3. CREATE PROFILE
        |--------------------------------------------------------------------------
        */


    $user = User::updateOrCreate(

    [
        'id' => $uuid
    ],

    [
        'email' => $request->email,

        'password' => Hash::make(
            $request->password
        ),

        'role' => 'user'
    ]

);




        event(new Registered($user));




        return redirect()

            ->route('login')

            ->with(
                'success',
                'Register successful'
            );

    }

}
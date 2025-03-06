<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Client;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;


class AuthController extends BaseController
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $client = Client::create([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'telephone_number' => $request['telephone_number'],
            'unit_number' => $request['unit_number'],
            'floor_number' => $request['floor_number'],
            'street' => $request['street'],
            'city' => $request['city'],
            'province' => $request['province'],
            'zip' => $request['zip'],
            'uuid' => Str::uuid(),
        ]);

        return User::create([
            'name' => $validatedData['first_name'] . ' ' . $validatedData['last_name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'client_id' => $client->id,
        ]);

        return $this->sendResponse([], 'User logout successfully.');
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $success['token'] =  $user->createToken('api')->plainTextToken;
            $success['name'] =  $user->name;
            $success['company_id'] =  $user->company_id;
            $success['role'] =  $user->getRoleNames()->toArray()[0] ?? null;

            $branches = auth()->user()->activeBranches()
                ->with([
                    'machines',
                    'region',
                    'province',
                    'city',
                    'barangay',
                ])
                ->get();

            $success['braches'] = $branches;

            return $this->sendResponse($success, 'User login successfully.');
        } else {
            return $this->sendError(
                'Unauthorised.', 
                ['error' => 'Unauthorised'],
                401,
                Config::get('app.status_codes')['unauthorised']
            );
        }
    }

    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();

        return $this->sendResponse([], 'User logout successfully.');
    }
}

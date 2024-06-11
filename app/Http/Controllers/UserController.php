<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.==
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
    }

    public function me()
    {
        return response()->json([
            'success' => true,
            'data' => auth()->user(),
            'address' => auth()->user()->address,
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {

            $validator = Validator::make($request->all(), [
                'phone' => 'numeric'
            ]);

            if ($request->username !== $user->username) {
                $validator->addRules([
                    'username' => 'unique:users',
                ]);
            }

            if ($request->email !== $user->email) {
                $validator->addRules([
                    'email' => 'unique:users|email',
                ]);
            }

            if ($validator->fails()) {
                return response()->json($validator->messages(), 422);
            }

            $user->update([
                'username' => $request->username,
                'email' => $request->email,
                'name' => $request->name,
                'phone' => $request->phone,
                'avatar' => $request->file('avatar'),
            ]);

            if ($user->address == null) {
                $user->address()->create([
                    'user_id' => $user->id,
                    'address_line' => $request->address_line,
                    'province' => $request->province,
                    'city' => $request->city,
                    'postal_code' => $request->postal_code,
                ]);
            } else {
                $user->address()->update([
                    'address_line' => $request->address_line,
                    'province' => $request->province,
                    'city' => $request->city,
                    'postal_code' => $request->postal_code,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data has been updated successfully',
                'data' => $user,
                'address' => $user->address,
            ]);
        }

        return response()->json([
            'success' => false,
        ], 401);
    }

    public function updatePassword(Request $request, $userId)
    {
        $user = User::find($userId);

        if ($user->id === auth()->id()) {
            $validation = Validator::make($request->all(), [
                'old_password' => 'required',
                'password' => 'required|confirmed',
                'password_confirmation' => 'required',
            ]);

            if ($validation->fails()) {
                return response()->json($validation->messages(), 422);
            }

            if (!Hash::check($request->old_password, $user->password)) {
                $validation->errors()->add('old_password_wrong', 'Wrong password');
                return response()->json($validation->messages(), 422);
            }

            $updated = $user->update([
                'password' => $request->password
            ]);

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => "Password has been changed",
                ]);
            }
        }

        return response()->json([
            'success' => false,
        ], 401);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
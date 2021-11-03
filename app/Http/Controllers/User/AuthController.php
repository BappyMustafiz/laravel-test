<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    /**
     * Confirm Invitation by using verification token
     */

    public function confirmInvitation(Request $request, $verification_token)
    {
        $user = User::where(['verification_token' => $verification_token, 'token_used' => 0])->first();

        if ($user) {
            $request->validate([
                'user_name' => 'required|string|min:4|max:20',
                'password' => 'required|string|min:6',
            ]);

            $pin = random_int(100000, 999999);

            $user->update([
                'user_name' => $request->user_name,
                'password' => bcrypt($request->password),
                'pin' => $pin
            ]);

            Mail::send('user.email.pin', ['pin' => $pin], function ($message) use ($user) {
                $message->subject('PIN for verification!.');
                $message->to($user->email);
            });

            return response()->json(['success' => true, 'message' => 'PIN sent successfully!'], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Token mismatch or already in used!'], 404);
        }
    }

    /**
     * Verify pin number
     */
    public function verifyPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|numeric|min:6',
        ]);

        $user = User::where(['pin' => $request->pin, 'token_used' => 0])->first();

        if ($user) {
            if ($request->pin == $user->pin) {
                $user->update([
                    'token_used' => 1,
                    'registered_at' => now(),
                ]);
                return new UserResource($user);
            } else {
                throw ValidationException::withMessages([
                    'pin' => ['Invalid PIN.'],
                ]);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'PIN mismatch or already in used!'], 404);
        }
    }

    /**
     * User Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where(['email' => $request->email, 'token_used' => 1])->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect or not verified.'],
            ]);
        }

        $user->token = $user->createToken('web')->plainTextToken;

        return new UserResource($user);
    }

    /**
     * Update user profile by id
     */
    public function updateProfile(Request $request, $id)
    {
        $request->validate([
            'name' => 'nullable|string',
            'user_name' => 'nullable|string|min:4|max:20',
            'password' => 'nullable|min:6',
            'email' => 'nullable|email',
            'avatar' => 'nullable|image|dimensions:max_width=256,max_height=256'
        ]);

        $user = User::find($id);

        if (!$user) return response()->json(['success' => false, 'message' => 'User not found!'], 404);

        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar')->store('avatars');
        } else {
            $avatar = $user->avatar;
        }

        $user->update([
            'name' => $request->name ?? $user->name,
            'user_name' => $request->user_name ?? $user->user_name,
            'password' => bcrypt($request->password) ?? $user->password,
            'email' => $request->email ?? $user->email,
            'avatar' => $avatar
        ]);

        return new UserResource($user);
    }

    /**
     * Logout user
     */
    public function logout()
    {
        Auth::guard('user')->user()->currentAccessToken()->delete();
    }
}

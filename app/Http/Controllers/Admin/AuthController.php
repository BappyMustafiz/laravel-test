<?php

namespace App\Http\Controllers\Admin;

use App\Events\InvitationMailEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\AuthResource;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Admin Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $admin->token = $admin->createToken('web')->plainTextToken;

        return new AuthResource($admin);
    }

    /**
     * Send Invitation mail
     */
    public function senInvitation(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $verifiedUserExist = User::where(['email' => $request->email, 'token_used' => 1])->first();

        if ($verifiedUserExist) {
            throw ValidationException::withMessages([
                'email' => ['The provided email already in use!.'],
            ]);
        }

        $user = User::where(['email' => $request->email, 'token_used' => 0])->first();

        $verification_token = openssl_random_pseudo_bytes(16);
        $verification_token = bin2hex($verification_token);


        if ($user) {
            $user->update([
                'verification_token' => $verification_token
            ]);
        } else {
            $user = User::create([
                'email' => $request->email,
                'verification_token' => $verification_token,
            ]);
        }

        $invitation_url = env('APP_URL') . "/api/user/confirm-invitation/" . $verification_token;

        Mail::send('user.email.invitation', ['invitation_url' => $invitation_url], function ($message) use ($user) {
            $message->subject('Accept invitation and signup.');
            $message->to($user->email);
        });

        return response()->json(['success' => true, 'message' => 'Invitation sent successfully!'], 200);
    }

    /**
     * Logout admin
     */
    public function logout()
    {
        Auth::guard('admin')->user()->currentAccessToken()->delete();
    }
}

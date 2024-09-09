<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Traits\HttpResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use HttpResponse;


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return response()->json([
            "message" => "Hello there"
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        // Validate request
        $request->validated($request->all());

        // Create user
        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "otp" => random_int(100000, 999999),
            "expires_at" => Carbon::now()->addMinutes(10),
            "verified" => false
        ]);

        // Log user details
        // \Log::info('Generated OTP: ' . $user->otp);

        // Send verification email
        Mail::send("email.verification", ["user" => $user], function ($message) use ($user) {
            $message->to($user->email)->subject("Verify your email address.");
        });

        // Return response
        return $this->success([
            "user" => $user
        ]);
    }

    /**
     * Send verification email.
     */

    public function verify(Request $request)
    {
        $request->validate([
            "email" => "required|string",
            "otp" => "required|string"
        ]);

        $user = User::where("email", $request->email)->first();

        if (!$user) {
            return response()->json([
                "message" => "This user does not exist."
            ]);
        }

        if ($user->otp !== $request->otp) {
            return response()->json([
                "message" => "Invalid otp code."
            ], 422);
        }

        if (Carbon::now()->greaterThan($user->expires_at)) {
            return response()->json([
                "message" => "Otp has expired."
            ], 422);
        }

        $user->update([
            "otp" => null,
            "expires_at" => null,
            "verified" => true,
        ]);

        return response()->json([
            'message' => "User verified successfully!",
        ], 200);
    }

    /**
     * Sign in function.
     */

    public function login(LoginUserRequest $request)
    {
        $request->validated($request->all());

        $user = User::where("email", $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                "message" => "Invalid Credentials",
                "status" => 401
            ], 401);
        }

        // Create token
        $token = $user->createToken("token")->plainTextToken;

        // Return response
        return $this->success([
            "token" => $token,
            "user" => $user
        ]);
    }


    /**
     * Resend Otp code.
     */
    public function resend(Request $request)
    {
        $request->validate([
            "email" => "required|string",
        ]);

        $user = User::where("email", $request->email)->firstOrFail();
        $user->otp = random_int(100000, 999999);
        $user->expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        // Resend the OTP via email
        Mail::send("email.verification", ["user" => $user], function ($message) use ($user) {
            $message->to($user->email)->subject("Verify your email address");
        });

        return response()->json([
            "message" => "OTP has been sent!"
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Logout user.
     */
    public function logout()
    {
        $user = auth()->user();

        if ($user) {
            $user->tokens()->delete();

            return response()->json([
                "message" => "Logged out"
            ]);
        }

        return response()->json([
            "message" => "Unauthenticated"
        ]);
    }
}

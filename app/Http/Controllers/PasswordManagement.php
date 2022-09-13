<?php

namespace App\Http\Controllers;

use App\Models\PasswordResets as ModelsPasswordManagement;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Carbon\Carbon;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class PasswordManagement extends Controller
{
    public function PassChangeMail(Request $request){
            $request->validate([
                "email"=>"required|email",
            ]);
            $user = User::where("email",$request->email)->first();
            if(!$user){
                return response([
                    "message"=>"this email is not registerd us.",
                    "status"=>"success",
                ],201);
            }else{
                $token = Str::random(60);
                $pass = new ModelsPasswordManagement();
                $pass->email = $request->email;
                $pass->token = $token;
                $pass->created_at = Carbon::now();
                $pass->save();
                $link = "http://127.0.0.1:8000/api/user/reset-password/" . $token; 
                $to = $request->email;
                Mail::send("passEmailChange", ['link'=>$link], function(Message $message) use ($to){
                    $message->subject("Reset Your Password");
                    $message->to($to);
                });
                return response([
                    "message"=>"Password reset link to email sent successfully",
                    "status"=>"success",
                ],201);
            }
    }

    public function resetPassword(Request $request){
        
        // delete token if time is over one mint. after issued
        $formatted = Carbon::now()->subMinutes(5)->toDateTimeString(); 
        ModelsPasswordManagement::where('created_at', '<=', $formatted)->delete();

        $request->validate([
            "password"=>"required|confirmed",
        ]);
        
        $user = ModelsPasswordManagement::where("token", $request->token)->first();
        
        if(!$user){
            return response([
                "message"=>"Token is invalid or expired",
                "status"=>"failed",
            ],404);
        }
        $user = User::where("email", $user->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // delete token after reset password
        ModelsPasswordManagement::where("email", $user->email)->delete();
        return response([
            "message"=>"Password Reset Success",
            "status"=>"success",
        ],200);
    }
}

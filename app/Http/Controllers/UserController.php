<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PhpParser\Node\Expr\FuncCall;
use Carbon\Carbon;

class UserController extends Controller
{
    public function newUser(Request $request){
        
        if($request->isMethod('POST')){
            
            $request->validate([
                "name"=>'required',
                "email"=>"required|email",
                "password"=>"required|confirmed",
            ]);

            if(User::where("email", $request->email)->first()){
                return response([
                    "message"=>"Email Address Already Exist",
                    "status"=>"failed",
                ],200);    
            }

            
            $user = User::create([
                "name"=>$request->name,
                "email"=>$request->email,
                "password"=>Hash::make($request->password)
            ]);

            $token = $user->createToken($request->email)->plainTextToken; 

            return response([
                "message"=>"User Registration Successfull",
                "status"=>"success",
                'token'=>$token
            ],201);

        }else {
            return response([
                "message"=>"Invalid Method Request",
                "status"=>"failed",
            ],405);
        }
    }

    public function login(Request $request){
        
        if($request->isMethod('POST')){
            $request->validate([
                "email"=>"required|email",
                "password"=>"required"
            ]);
            $user = User::where("email", $request->email)->first();
            if($user && Hash::check($request->password, $user->password)){
                $token = $user->createToken($request->email)->plainTextToken;
                
                return response([
                    "message"=>"login successfull",
                    "status"=>"success",
                    'token'=>$token,
                ],201);
                
            }else {
                return response([
                    "message"=>"User credentials does found",
                    "status"=>"error",
                ],201);
            }
        }else {
            return response([
                "message"=>"Invalid Method Request",
                "status"=>"failed",
            ],405);
        }
    }

    public function logout(Request $request){

    if($request->isMethod('POST')){
        
        $request->user()->tokens()->delete();
        // auth()->user()->tokens()->delete();
        
        return response([
            "message"=>"Logout success",
            "status"=>"success"
        ],200);

        }else {

            return response([
                "message"=>"Invalid Method Request for logout",
                "status"=>"failed",
            ],405);
        }   
    }
    
    public function logged_user(){
        
        $loggedUser = auth()->user();
            return response([
                "message"=>"Logged User Data",
                "status"=>"success",
                'data'=>$loggedUser,
            ],200);   
    }

    
    public function changePassword(Request $request){
        
        $request->validate([
            "password"=>"required|confirmed",
        ]);
        
        $loggedUser = auth()->user();
        $loggedUser->password = Hash::make($request->password);
        $loggedUser->save();

        return response([
            "message"=>"Password change successfully",
            "status"=>"success",
        ],200);   
    }
    
}

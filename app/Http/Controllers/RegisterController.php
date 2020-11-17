<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class RegisterController extends Controller
{
    public function post(Request $request)
    {
        $now = Carbon::now();
        $hashed_password = Hash::make($request->password);
        $param = [
            'name' => $request->name,
            'email' => $request->email,
            'user_id' => $request->user_id,
            'tell' => $request->tell,
            'password' => $hashed_password,
            'account' => $request->account,
            'introducer' => $request->introducer,
            'created_at' => $now,
            'updated_at' => $now

        ];
        DB::table('users')->insert($param);
        $item = DB::table('users')->select('id')->where('email',$request->email)->get();
        $number = $item[0]->id;
        DB::table('authorities')->insert(
            [
                'boss_id' => $request->introducer,
                'subordinate_id'=>$number,
                'created_at' => $now,
                'updated_at' => $now
            ]
        );
        return response()->json([
            'message' => 'success!',
            'data' => $param
        ], 200);
    }
}
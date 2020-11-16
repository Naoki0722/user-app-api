<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function all(Request $request)
    {
        $item = DB::table('users')->get();
        return response()->json([
            'message' => 'user info success get!',
            'data' => $item
        ], 200);
    }

    public function get(Request $request)
    {
        $item = DB::table('users')->where('email', $request->email)->get();
        return response()->json([
            'message' => 'user info success get!',
            'data' => $item
        ],200);
    }

    public function put(Request $request)
    {
        $param = [
            'name' => $request->name,
            'email' => $request->email,
            'tell' => $request->tell,
            'user_id' => $request->user_id,
            'account' => $request->account,
        ];
        DB::table('users')
            ->where('email', $request->email)
            ->update($param);
        return response()->json([
            'message' => 'success update!',
            'data' => $param
        ], 200);
    }

    public function delete(Request $request)
    {
        $items = DB::table('users')
        ->where('email', $request->email)->delete();
            return response()->json([
                'message' => 'success delete!',
                'data' => $items
            ], 200);
    }

}

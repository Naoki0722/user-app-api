<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;


class RegisterController extends Controller
{
    public function post(Request $request)
    {
        // $request->validate([
        //     'image' => 'required|file|image|mimes:png,jpeg'
        // ]);
        $upload_image = $request->file('fileInfo');
        dd($upload_image);
        // if($upload_image) {
            $image_name = $upload_image->getClientOriginalName();
            $save = Storage::disk('s3')->putFileAs('/user', $upload_image, $image_name, 'public');
            $path= Storage::disk('s3')->url($save);
        // }
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
            'image_path' => $path,
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
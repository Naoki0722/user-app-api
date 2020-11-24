<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Str;
use finfo;
use Illuminate\Support\Facades\Storage;


class RegisterController extends Controller
{
    public function post(Request $request)
    {
        $file_name = $request->img;
        $file_name = preg_replace('/^data:image.*base64,/', '', $file_name);
        $file_name = str_replace('', '+', $file_name);
        $image = base64_decode($file_name);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_buffer( $finfo, $image);
        $extensions = [
            'image/gif' => 'gif',
            'image/jpeg' => 'jpeg',
            'image/png' => 'png',
        ];
        // 保存新ファイル名(ランダム生成)
        $random_str = Str::random(10);
        $filename = $random_str . '.' . $extensions[$mime_type];

        $store_dir = 'user';
        $storefile = sprintf('%s/%s', $store_dir, $filename);
        Storage::disk('s3')->put($storefile, $image);
        $image_path = Storage::disk('s3')->url($storefile);
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
            'image_path' => $image_path,
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
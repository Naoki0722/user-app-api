<?php

namespace App\Http\Controllers;

use App\Models\Authority;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Str;
use finfo;
use Illuminate\Support\Facades\Storage;
use binary;
use Exception;

/**
 * 機能一覧
 *
 * 会員登録をする
 */
class RegisterController extends Controller
{
    /**
     * 会員登録をする
     *
     * @param Request $request
     * @return void
     */
    public function post(Request $request)
    {
        $now = Carbon::now();
        $imagePath = $this->uploadImage($request->img);
        $param = [
            'name' => $request->name,
            'email' => $request->email,
            'user_id' => $request->user_id,
            'tell' => $request->tell,
            'password' => Hash::make($request->password),
            'account' => $request->account,
            'introducer' => $request->introducer,
            'directly' => $request->directly,
            'image_path' => $imagePath
        ];
        $user = User::create($param);
        //authoritiesテーブルにデータを格納(自分がsubordinate_idとなる)
        Authority::create([
            'boss_id' => $request->introducer,
            'subordinate_id'=> $user->id
        ]);
        //authoritiesテーブルにデータを格納(自分がboss_idとなり、直下の人がsubordinate_idとなる)
        Authority::create([
            'boss_id' => $user->id,
            'subordinate_id' => $request->directly
        ]);


        $get_bossid = DB::table('authorities')->select('boss_id')->whereNotNull('subordinate_id')->where('subordinate_id', $request->introducer)->get();
        foreach ($get_bossid as $key => $value) {
            $get_boss = $value->boss_id;
            //authoritiesテーブルにデータを格納(直紹介の人以上のboss_idを取得し、直紹介以上のbossと紐付け)
            DB::table('authorities')->insert(
                [
                    'boss_id' => $get_boss,
                    'subordinate_id' => $user->id,
                    'created_at' => $now,
                    'updated_at' => $now
                ]
            );
        };
        $get_directid = DB::table('authorities')->select('subordinate_id')->whereNotNull('boss_id')->where('boss_id', $request->directly)->get();
        foreach ($get_directid as $key => $value) {
            $get_direct = $value->subordinate_id;
            //authoritiesテーブルにデータを格納(直下の人以下のsubordinate_idを取得し、直下以上と紐付け)
            DB::table('authorities')->insert(
                [
                    'boss_id' => $user->id,
                    'subordinate_id' => $get_direct,
                    'created_at' => $now,
                    'updated_at' => $now
                ]
            );
        };
        return response()->json([
            'message' => 'success!',
            'data' => $param
        ], 200);
    }

    /**
     * 画像アップロード処理
     *
     * フロント側より画像をbase64でエンコードした状態でリクエストされる。
     * でコード後、拡張子を取得し、どのファイル形式かを確認。
     * AWS S3に user/画像ファイル名 で保存する
     * 保存したURLパスを取得する(DB保存用)
     *
     * @param string $img base64形式のバイナリーデータ
     * @return string 画像URL
     */
    private function uploadImage($img)
    {
        // $file_name = preg_replace('/^data:image.*base64,/', '', $img);
        // $image = base64_decode(str_replace('', '+', $file_name));
        // $mime_type = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $image);
        $image = base64_decode($img);
        $mime_type = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $image);
        $extensions = [
            'image/gif'  => 'gif',
            'image/jpeg' => 'jpeg',
            'image/png'  => 'png',
        ];
        $fileName = Str::random(10) . '.' . $extensions[$mime_type];
        $putS3File = "user/{$fileName}";
        $putFile = Storage::disk('s3')->put($putS3File, $image);
        if ($putFile) {
            return Storage::disk('s3')->url($putS3File);
        }
        return throw new Exception("failed s3 upload");
    }
}

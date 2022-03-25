<?php

namespace App\Http\Controllers;

use App\Models\Authority;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * 機能一覧
 *
 * ログインする
 * 会員登録をする
 * 画像アップロード処理を実行する
 */
class AuthController extends Controller
{
    /**
     * ログインする。
     *
     * @param Request $request 会員登録時の入力データ
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $status = Response::HTTP_CREATED;
            $message = 'user create success';
        } else {
            [$status, $message] = self::outputError('ログイン失敗', 'user create failed');
        }
        return response()->json([
            'status' => $status,
            'message' => $message
        ], $status);
    }

    /**
     * 会員登録をする
     *
     * @param Request $request 会員登録時の入力データ
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $imagePath = $this->uploadImage($request->img);
            $param = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'tell' => $request->tell,
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
            //authoritiesテーブルにデータを格納(直紹介の人以上のboss_idを取得し、直紹介以上のbossと紐付け)
            $bossesData = Authority::getBoss($request->introducer);
            foreach ($bossesData as $bossData) {
                Authority::create([
                    'boss_id' => $bossData->boss_id,
                    'subordinate_id' => $user->id,
                ]);
            };
            //authoritiesテーブルにデータを格納(直下の人以下のsubordinate_idを取得し、直下以上と紐付け)
            $directoriesData = Authority::getDirectly($request->directory);
            foreach ($directoriesData as $directlyData) {
                Authority::create([
                    'boss_id' => $user->id,
                    'subordinate_id' => $directlyData->subordinate_id,
                ]);
            };
            $status = Response::HTTP_CREATED;
            $message = 'user create success';
        } catch (Throwable $e) {
            [$status, $message] = self::outputError($e, 'user create failed');
        }
        return response()->json([
            'status' => $status,
            'message' => $message
        ], $status);
    }

    /**
     * ログアウト処理をする。
     *
     */
    public function logout(Request $request)
    {
        return response()->json(['auth'=>false], 200);
    }

    /**
     * 画像アップロード処理を実行する。
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

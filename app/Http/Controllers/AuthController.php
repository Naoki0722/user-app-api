<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\Authority;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * 機能一覧
 *
 * ログインする
 * ログアウト処理をする
 * 会員登録をする
 * 上司関係を登録する
 * 部下関係を登録する
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
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => 'required',
            ]);
            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();
                return self::respondJson(Response::HTTP_OK, 'user login success');
            }
            return self::respondJson(Response::HTTP_UNAUTHORIZED, 'user login failed auth');
        } catch (Exception $e) {
            [$status, $message] = self::outputError($e->getMessage(), 'user login failed');
            return self::respondJson($status, $message);
        }
    }

    /**
     * ログアウト処理をする。
     *
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return self::respondJson(Response::HTTP_OK, 'user logout success');
    }

    /**
     * 会員登録をする
     *
     * 会員登録後、個人情報閲覧権限制限のため、authoritiesテーブルに関係性のデータを格納する
     *
     * @param Request $request 会員登録時の入力データ
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();
        try {
            $imagePath = $this->uploadImageToS3($request->img);
            $user = User::regsiterUser($request, $imagePath);
            if (isset($request->introducer)) {
                self::registerBossRelation($request->introducer, $user->id);
            }
            if (isset($request->directory)) {
                self::registerDirectoryRelation($request->directory, $user->id);
            }
            DB::commit();
            $status = Response::HTTP_CREATED;
            $message = 'user create success';
        } catch (Exception $e) {
            DB::rollback();
            [$status, $message] = self::outputError($e->getMessage(), 'user create failed');
        }
        return self::respondJson($status, $message);
    }

    /**
     * 上司関係を登録する。
     *
     * 上司がいるとき、直上司以外の上司との関係性も登録する
     * 直の上司を登録(自分が部下となる)
     * その上の上司情報も登録(自分が部下となる)
     *
     * @param int $introducer 直の上司
     * @param int $userId 自身のユーザーID
     * @return void|array
     */
    private static function registerBossRelation($introducer, $userId)
    {
        try {
            //authoritiesテーブルにデータを格納(自分がsubordinate_idとなる)
            //authoritiesテーブルにデータを格納(直紹介の人以上のboss_idを取得し、直紹介以上のbossと紐付け)
            Authority::registerAuthority($introducer, $userId);
            $bossesData = Authority::getBoss($introducer);
            foreach ($bossesData as $bossData) {
                Authority::registerAuthority($bossData->boss_id, $userId);
            }
        } catch (Exception $e) {
            [$status, $message] = self::outputError($e->getMessage(), 'user create failed');
            return [$status, $message];
        }
        return;
    }

    /**
     * 部下関係を登録する。
     *
     * 部下がいるとき、直の部下以外の部下の関係性も登録する
     * 直の部下情報登録(自身が上司となる)
     * その下の部下情報も登録(自身が上司となる)
     *
     * @param int $directory 直の部下
     * @param int $userId 自身のユーザーID
     * @return void|array
     */
    private static function registerDirectoryRelation($directory, $userId)
    {
        try {
            //authoritiesテーブルにデータを格納(自分がboss_idとなり、直下の人がsubordinate_idとなる)
            //authoritiesテーブルにデータを格納(直下の人以下のsubordinate_idを取得し、直下以上と紐付け)
            Authority::registerAuthority($userId, $directory);
            $directoriesData = Authority::getDirectly($directory);
            foreach ($directoriesData as $directlyData) {
                Authority::registerAuthority($userId, $directlyData->subordinate_id);
            }
        } catch (Exception $e) {
            [$status, $message] = self::outputError($e->getMessage(), 'user create failed');
            return [$status, $message];
        }
        return;
    }

    /**
     * 画像アップロード処理を実行する。
     *
     * フロント側より画像をbase64でエンコードした状態でリクエストされる。
     * でコード後、拡張子を取得し、どのファイル形式かを確認。
     * AWS S3に user/画像ファイル名 で保存する
     * 保存したURLパスを取得する(DB保存用)
     *
     * @param string $binaryImage base64形式のバイナリーデータ
     * @return string 画像URL
     */
    private function uploadImageToS3($binaryImage)
    {
        $decodeImage = base64_decode(
            str_replace('', '+', preg_replace('/^data:image.*base64,/', '', $binaryImage))
        );
        $mimeType = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $decodeImage);
        $extensions = [
            'image/gif'  => 'gif',
            'image/jpeg' => 'jpeg',
            'image/png'  => 'png',
        ];
        $imageFileName = Str::random(10) . '.' . $extensions[$mimeType];
        $s3Folder = "user/{$imageFileName}";
        $putFile = Storage::disk('s3')->put($s3Folder, $decodeImage);
        if ($putFile) {
            return Storage::disk('s3')->url($s3Folder);
        }
        return null;
    }
}

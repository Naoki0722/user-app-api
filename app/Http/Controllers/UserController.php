<?php

namespace App\Http\Controllers;

use App\Models\Authority;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * 機能一覧
 *
 * ユーザー情報を全件取得する
 * ユーザー情報を更新する
 * 退会処理をする
 */
class UserController extends Controller
{
    public function person($id)
    {
        $item = DB::table('users')
                ->join('authorities', 'users.id', '=', 'authorities.subordinate_id')
                ->where('boss_id', $id)
                ->get();
        return response()->json([
            'message' => 'user info success get!',
            'data' => $item
        ], 200);
    }

    /**
     * ユーザー情報を全件取得する。
     *
     * 個人情報の観点から、名前のみ取得
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchAllUser()
    {
        return self::respondJson(
            Response::HTTP_OK,
            'user info success get!',
            User::get(['id', 'name'])
        );
    }

    /**
     * ユーザー情報を更新する。
     *
     * @param Request $request 更新データ
     * @param int $id ユーザーID
     * @return \Illuminate\Http\JsonResponse
     */
    public function editUser(Request $request, $id)
    {
        if (Auth::id() === $id) {
            $data = $request->all();
            User::find($id)->update($data);
            return self::respondJson(Response::HTTP_OK, 'success update!', $data);
        }
        return self::respondJson(Response::HTTP_INTERNAL_SERVER_ERROR, 'cannot update except your own');
    }

    /**
     * 退会処理をする。
     *
     * @param int $id ユーザーID
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        if (Auth::id() === $id) {
            User::find($id)->delete();
            Authority::where('boss_id', $id)->orWhere('subordinate_id', $id)->delete();
            return self::respondJson(Response::HTTP_OK, 'success delete!');
        }
        return self::respondJson(Response::HTTP_INTERNAL_SERVER_ERROR, 'cannot delete except your own');
    }
}

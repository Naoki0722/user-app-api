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
    public function person(Request $request)
    {
        $item = DB::table('users')
                ->join('authorities', 'users.id', '=', 'authorities.subordinate_id')
                ->where('boss_id', $request->id)
                ->get();
        return response()->json([
            'message' => 'user info success get!',
            'data' => $item
        ], 200);
    }

    /**
     * ユーザー情報を全件取得する。
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchAllUser()
    {
        return self::respondJson(
            Response::HTTP_OK,
            'user info success get!',
            User::all()
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
            return response()->json([
                'message' => 'success update!',
                'data' => $data
            ], 200);
        } else {
            return response()->json([
                'message' => '自分以外のデータは変更できません'
            ], 500);
        }
    }

    /**
     * 退会処理をする。
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        if (Auth::id() === $id) {
            User::find($id)->delete();
            Authority::where('boss_id', $id)->orWhere('subordinate_id', $id)->delete();
            return response()->json([
                'message' => 'success delete!'
            ], 200);
        } else {
            return response()->json([
                'message' => '自分以外のデータは削除できません'
            ], 500);
        }
    }
}

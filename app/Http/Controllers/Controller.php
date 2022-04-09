<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * 機能一覧
 *
 * エラーを出力する
 * Jsonデータを返答する
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * エラーを出力する。
     *
     * @param Throwable $e
     * @param string $errorMessage
     * @return \Illuminate\Http\JsonResponse
     */
    public static function outputError($errorDetail, $returnMessage)
    {
        Log::info('error detail', [$errorDetail]);
        $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        return [$status, "{$returnMessage}.detail：{$errorDetail}"];
    }

    /**
     * Jsonデータを返答する
     *
     * @param int $status ステータスコード
     * @param string $message 返答メッセージ
     * @return \Illuminate\Http\Response
     */
    public static function respondJson($status, $message)
    {
        return response()->json([
            'status' => $status,
            'message' => $message
        ], $status);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

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
    public static function outputError($e, $errorMessage)
    {
        Log::info('error detail', [$e->getMessage()]);
        $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        $message = $errorMessage;
        return [$status, $message];
    }
}

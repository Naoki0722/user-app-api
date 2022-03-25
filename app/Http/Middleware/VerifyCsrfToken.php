<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     * ログイン以外の認証を必要としないルーティングを設定しないと毎回csrfトークンの取得が必要になる。
     * 共通ページや認証不要のページは指定する。
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}

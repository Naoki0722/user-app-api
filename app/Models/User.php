<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'tell',
        'account',
        'introducer',
        'directory',
        'image_path',
        'account',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * ユーザー登録処理
     *
     * @param [type] $request 登録情報
     * @param [type] $imagePath 画像URL
     * @return \App\Models\User
     */
    public static function regsiterUser($request, $imagePath)
    {
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
        return User::create($param);
    }
}

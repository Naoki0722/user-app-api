<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Authority extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'boss_id',
        'subordinate_id'
    ];

    public static function getBoss($introducer)
    {
        return self::whereNotNull('subordinate_id')
            ->where('subordinate_id', $introducer)
            ->get();
    }

    public static function getDirectly($directly)
    {
        return self::whereNotNull('boss_id')
            ->where('boss_id', $directly)
            ->get();
    }
}

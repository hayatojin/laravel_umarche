<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Product;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Productテーブルと多対多のリレーション（複数のユーザーが複数の商品を持つ）
    public function product()
    {
        return $this->belongsToMany(Product::class, 'carts') // 中間テーブルを定義する時は、第二引数にテーブル名をつける
        ->withPivot(['id', 'quantity']); // 中間テーブルのカラム取得
    }
}

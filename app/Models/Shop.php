<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Owner;
use App\Models\Prodcut;

class Shop extends Model
{
    use HasFactory;

    // Shop登録にあたりShopのデータを更新するため、更新対象のカラムをfillableで限定
    protected $fillable = [
        'owner_id',
        'name',
        'information',
        'filename',
        'is_selling'
    ];

    // Ownerとのリレーション（1対1）
    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    // Prodcutとのリレーション（1対多）※1つのショップが複数の商品をもつ
    public function product()
    {
        return $this->hasMany(Product::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Owner;

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
}

<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Shop;
use Illuminate\Support\Facades\Storage;
use InterventionImage;
use App\Http\Requests\UploadImageRequest;
use App\Services\ImageService;

class ShopController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:owners');

        // 他のユーザーの編集画面を見れなくする
        $this->middleware(function ($request, $next) {
            // dd($request->route());

            // ShopオーナーのIDと、ログインIDを比較（合っていればOK・違ったらエラー表示）
            $id = $request->route()->parameter('shop'); //shopのid取得
                if(!is_null($id)){ // null判定
                    $shopsOwnerId = Shop::findOrFail($id)->owner->id;
                    $shopId = (int)$shopsOwnerId; // キャスト 文字列→数値に型変換 
                    $ownerId = Auth::id();

                if($shopId !== $ownerId){ // 同じでなかったら
                    abort(404); // 404画面表示 }
                    }
                }
                return $next($request);
        });
    }

    public function index()
    {
        $ownerId = Auth::id(); // ログインIDを取得
        $shops = Shop::where('owner_id', $ownerId)->get(); // ショップテーブルと紐づくログインIDを検索（多分）

        return view('owner.shops.index', compact('shops'));
    }

    // edit/{shop}の{}には、オーナーIDが入ってくる（オーナー側のルーティングによって呼び出される）
    // ルーティングからパラメータを取得している
    public function edit($id)
    {
        $shop = Shop::findOrFail($id);
        // dd(Shop::findOrFail($id));

        return view('owner.shops.edit', compact('shop'));
    }

    public function update(UploadImageRequest $request, $id)
    {
        $imageFile = $request->image; //一時保存 

        if(!is_null($imageFile) && $imageFile->isValid() )
        {
            $fileNameToStore = ImageService::upload($imageFile, 'shops');
        }

        return redirect()->route('owner.shops.index');
    }
}

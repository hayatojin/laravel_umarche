<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Shop;
use Illuminate\Support\Facades\Storage;
use InterventionImage;

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

    public function update(Request $request, $id)
    {
        $imageFile = $request->image; //一時保存 

        if(!is_null($imageFile) && $imageFile->isValid() )
        {
            // Storage::putFile('public/shops', $imageFile); // リサイズなしの場合の処理

            $fileName = uniqid(rand().'_'); // ランダム文字を生成
            $extension = $imageFile->extension(); // 拡張子を取得
            $fileNameToStore = $fileName. '.' . $extension; // ランダム文字と拡張子をくっつける
            $resizedImage = InterventionImage::make($imageFile)->resize(1920, 1080)->encode(); // リサイズ処理

            Storage::put('public/shops/' . $fileNameToStore, $resizedImage );
        }

        return redirect()->route('owner.shops.index');
    }
}

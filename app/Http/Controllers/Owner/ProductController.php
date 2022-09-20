<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Image;
use App\Models\Product;
use App\Models\SecondaryCategory;
use App\Models\Owner;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:owners');

        $this->middleware(function ($request, $next) {

            $id = $request->route()->parameter('product'); //imageのid取得
                if(!is_null($id)){
                    // オーナーが所有する（オーナーに紐づいた）商品だけに限定するが、
                    // Productモデルにはowner_idが存在しない。
                    // そのため、ProductからShopへ繋いで、ShopからOwneridを取得する
                    $ProductsOwnerId = Product::findOrFail($id)->shop->owner->id;
                    $ProductId = (int)$ProductsOwnerId; // キャスト 文字列→数値に型変換 

                if($ProductId !== Auth::id()){
                    abort(404);
                    }
                }
                return $next($request);
        });
    }

    
    public function index()
    {
        // ログインしているオーナーが作っているプロダクトをindexとして表示する
        // auth::idでログインオーナーを取り、shopに繋いで、productをとる　※以下42行目が本項目で解説してるコード
        // $products = Owner::findOrFail(Auth::id())->shop->product;

        // 42行目のコードでは、N+1問題が発生しているため、withメソッドでまとめる
        // さらに、リレーション先のEagerロード解消のために、「.」でつなぐ
        $ownerInfo = Owner::with('shop.product.imageFirst')
        ->where('id', Auth::id())->get();

        // dd($ownerInfo);

        // 以下コメントアウトは、filenameを取得するための確認（ddで確認すると、配列の中にさらに配列があるため、二重でforeachが必要）
        // foreach($ownerInfo as $owner){
        //     // dd($owner->shop->product);
        //     foreach($owner->shop->product as $product){
        //         dd($product->imageFirst->filename);
        //     }
        // }

        return view('owner.products.index', compact('ownerInfo'));
    }

    
    public function create()
    {
        //
    }

    
    public function store(Request $request)
    {
        //
    }

    
    public function show($id)
    {
        //
    }

   
    public function edit($id)
    {
        //
    }

   
    public function update(Request $request, $id)
    {
        //
    }

    
    public function destroy($id)
    {
        //
    }
}

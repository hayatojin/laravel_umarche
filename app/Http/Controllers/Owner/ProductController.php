<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Image;
use App\Models\Product;
use App\Models\Stock;
use App\Models\PrimaryCategory;
use App\Models\Owner;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;

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
        // ショップテーブルから、ログインしているowner_idで絞り込む
        $shops = Shop::where('owner_id', Auth::id())
        ->select('id', 'name')
        ->get();

        // イメージテーブルから絞り込み
        $images = Image::where('owner_id', Auth::id())
        ->select('id', 'title','filename')
        ->orderBy('updated_at', 'desc')
        ->get();

        // PrimaryCategoryモデルから、SecondaryCategoryのリレーションを取ってくる
        $categories = PrimaryCategory::with('secondary')
        ->get();

        return view('owner.products.create', compact('shops', 'images', 'categories'));
    }

    
    public function store(Request $request)
    {
        // dd($request);
        $request->validate([
            'name' => 'required|string|max:50',
            'information' => 'required|string|max:1000',
            'price' => 'required|integer',
            'sort_order' => 'nullable|integer',
            'quantity' => 'required|integer',
            'shop_id' => 'required|exists:shops,id',
            'category' => 'required|exists:secondary_categories,id',
            'image1' => 'nullable|exists:images,id',
            'image2' => 'nullable|exists:images,id',
            'image3' => 'nullable|exists:images,id',
            'image4' => 'nullable|exists:images,id',
            'is_selling' => 'required'
        ]);

        try{
            DB::transaction(function()use($request){
                $product = Product::create([
                    'name' => $request->name,
                    'information' => $request->information,
                    'price' => $request->price,
                    'sort_order' => $request->sort_order,
                    'shop_id' => $request->shop_id,
                    'secondary_category_id' => $request->category,
                    'image1' => $request->image1,
                    'image2' => $request->image2,
                    'image3' => $request->image3,
                    'image4' => $request->image4,
                    'is_selling' => $request->is_selling
                ]);

                Stock::create([
                    'product_id' => $product->id,
                    'type' => 1,
                    'quantity' => $request->quantity,
                ]);
            }, 2); // 「2」はデッドロック処理（トランザクションの最大試行回数）

        }catch(Throwable $e){
            Log::error($e);
            throw $e;
        }

        return redirect()
        ->route('owner.products.index')
        ->with(['message' => '商品を登録しました。',
        'status' => 'info']);
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

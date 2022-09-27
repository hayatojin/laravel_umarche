<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:users');

        $this->middleware(function ($request, $next) {

            $id = $request->route()->parameter('item');
                if(!is_null($id)){
                    // ProductテーブルのIDが、入ってきたIDと一致していれば取得する
                    $itemId = Product::availableItems()->where('products.id', $id)->exists(); // existsは入ってきたIDが存在するかどうかを確かめる

                if(!$itemId){
                    abort(404);
                    }
                }
                return $next($request);
        });
    }

    public function index()
    {
        $products = Product::availableItems()->get(); // Productモデルで定義したスコープを利用

        return view('user.index', compact('products'));
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        $quantity = Stock::where('product_id', $product->id)->sum('quantity');

        // 数量は9までしか選べないようにする（9以上ある場合は、9に設定する）
        if($quantity > 9){
            $quantity = 9;
        }

        return view('user.show', compact('product', 'quantity'));
    }
}

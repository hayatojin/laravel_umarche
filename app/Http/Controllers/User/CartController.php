<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function add(Request $request)
    {
        // ログインユーザーがカートに入れた商品を取得（where2つでAND条件）
        $itemInCart = Cart::where('product_id', $request->product_id)
            ->where('user_id', Auth::id())->first();

        // 現在のカートに入っている商品の数に、これからカートに入る数を足す
        // カート内の商品の数が0なら、商品を新たに追加する
        if($itemInCart){
            $itemInCart->quantity += $request->quantity; 
            $itemInCart->save();
        } else {
            Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
            ]);
        }
        dd('テスト');
    }
}

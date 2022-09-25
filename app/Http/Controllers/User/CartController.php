<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\User;
use App\Models\Stock;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $user = User::findOrFail(Auth::id()); // ログインしているユーザー情報を取得
        $products = $user->products; // ログインユーザに紐づく商品を取得（多対多のリレーション設定でuserとproductsを紐付けているため、取得できる）
        $totalPrice = 0; // 合計金額を一旦、初期化

        foreach($products as $product){
            $totalPrice += $product->price * $product->pivot->quantity; // 合計金額を計算（金額×数量）
        }
        
        // dd($products, $totalPrice);

        return view('user.cart', compact('products', 'totalPrice'));
    }


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
                'quantity' => $request->quantity
            ]);
        }
        
        // カートに商品を入れたらindexのルーティングを飛ばし、Cartコントローラのindexメソッドを実行させる
        return redirect()->route('user.cart.index');
    }

    public function delete($id)
    {
        Cart::where('product_id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        return redirect()->route('user.cart.index');
    }

    // Stripe API
    public function checkout()
    {
        $user = User::findOrFail(Auth::id()); // ログインしているユーザー情報を取得
        $products = $user->products;

        $lineItems = [];

        foreach($products as $product){
            $quantity = '';
            $quantity = Stock::where('product_id', $product->id)->sum('quantity'); // 現在の在庫数を確認

            // カート内の商品数量と、Stockテーブル（在庫）内の商品数量を比べる（Stockテーブルの在庫数量よりも、カート内の商品数が多い場合、購入できない）
            if($product->pivot->quantity > $quantity){
                return redirect()->route('user.cart.index');
            } else {
            // 在庫よりも、カート内の商品数が少なければ、購入できる（購入処理：Stripe APIでAPI用の変数に値を渡す）
                $lineItem = [
                    'name' => $product->name,
                    'description' => $product->information,
                    'amount' => $product->price,
                    'currency' => 'JPY',
                    'quantity' => $product->pivot->quantity,
                ];

                array_push($lineItems, $lineItem); //foreachで回したそれぞれの商品の中身を、$lineItemsに追加する
            }
        }
        // dd($lineItems);

        foreach($products as $product){
            Stock::create([
                'product_id' => $product->id,
                'type' => \Constant::PRODUCT_LIST['reduce'],
                'quantity' => $product->pivot->quantity * -1
            ]);
        }

        dd('test');

        \Stripe\Stripe::setApiKey(env(' STRIPE_SECRET_KEY '));

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [$lineItems],
            'mode' => 'payment',
            'success_url' => route('user.cart.success'),
            'cancel_url' => route('user.cart.cancel'),
        ]);

        $publickey = env(' STRIPE_PUBLIC_KEY ');

        return view('user.checkout', compact('sesshon', 'publickey'));
    }
}

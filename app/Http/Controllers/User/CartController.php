<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\User;
use App\Models\Stock;
use Illuminate\Support\Facades\Auth;
use App\Services\CartService;
use App\Jobs\SendThanksMail;
use App\Jobs\SendOrderedMail;

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
        $user = User::findOrFail(Auth::id());
        $products = $user->products;
        
        $lineItems = [];
        foreach($products as $product){
            $quantity = '';
            $quantity = Stock::where('product_id', $product->id)->sum('quantity');

            if($product->pivot->quantity > $quantity){
                return redirect()->route('user.cart.index');
            } else {
                $lineItem = [
                    'name' => $product->name,
                    'description' => $product->information,
                    'amount' => $product->price,
                    'currency' => 'jpy',
                    'quantity' => $product->pivot->quantity,
                ];
                array_push($lineItems, $lineItem);    
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

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
        \Stripe\Stripe::setApiVersion('2020-08-27');

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [$lineItems],
            'mode' => 'payment',
            'success_url' => route('user.cart.success'),
            'cancel_url' => route('user.cart.cancel'),
        ]);

        $publicKey = env('STRIPE_PUBLIC_KEY');

        return view('user.checkout', 
            compact('session', 'publicKey'));
    }


    public function success()
    {
        // メール用のサービスを読み込む
        $items = Cart::where('user_id', Auth::id())->get(); // ログインしてるユーザのカート情報を取得
        $products = CartService::getItemsInCart($items); // 上記内容を引き継ぐため、引数に$itemsを設定
        $user = User::findOrFail(Auth::id());
        
        // ユーザー側へのメール送信
        SendThanksMail::dispatch($products, $user);

        // オーナー側へのメール送信では、複数名へメールを送る可能性があるため、foreachを使う
        foreach($products as $product)
        {
            SendOrderedMail::dispatch($product, $user);
        }

        // dd('ユーザーメール送信テスト');

        Cart::where('user_id', Auth::id())->delete();

        return redirect()->route('user.items.index'); // 商品一覧画面へ戻す
    }


    public function cancel()
    {
        $user = User::findOrFail(Auth::id());

        foreach($user->products as $product){
            Stock::create([
                'product_id' => $product->id,
                'type' => \Constant::PRODUCT_LIST['add'],
                'quantity' => $product->pivot->quantity
            ]);
        }

        return redirect()->route('user.cart.index'); // カート画面へ戻す
    }
}

<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Cart;

class CartService
{
    public static function getItemsInCart($items)  // CartController checkoutメソッド内で、呼び出されたgetItemsInCartメソッドの引数を受け取る
    {
        // 空の配列を作成（後で、ここにarray_pushで情報を入れるための箱作成）
        $products = [];

        foreach($items as $item)
        {
            $p = Product::findOrFail($item->product_id);
            $owner = $p->shop->owner->select('name', 'email')->first()->toArray(); //オーナー情報
            $values = array_values($owner); //連想配列の値を取得
            $keys = ['ownerName', 'email'];
            $ownerInfo = array_combine($keys, $values); // オーナー情報のキーを変更

            $product = Product::where('id', $item->product_id)
            ->select('id', 'name', 'price')->get()->toArray(); // 商品情報の配列

            $quantity = Cart::where('product_id', $item->product_id)
            ->select('quantity')->get()->toArray(); // 在庫数の配列

            // 配列の結合 
            // $productと$quantityは、配列の中に配列が入る構造になっているため、[0]で要素を取得する
            $result = array_merge($product[0], $ownerInfo, $quantity[0]);

            array_push($products, $result); //配列に追加
        }
        return $products;
    }
}
<?php

namespace App\Constants;

Class Common
{
    const PRODUCT_ADD = '1';
    const PRODUCT_REDUCE = '2';

    const PRODUCT_LIST = [
        'add' => self::PRODUCT_ADD, // クラスの中でconstを選択する場合は、self::を使う
        'reduce' => self::PRODUCT_REDUCE
    ];
}
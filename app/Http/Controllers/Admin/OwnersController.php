<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Owner; // Eloquent
use Illuminate\Support\Facades\DB; // クエリビルダ
use Carbon\Carbon; // Carbonインスタンス

class OwnersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }


    public function index()
    {
        // Carbonのテスト
        // $data_now = Carbon::now();
        // $data_parse = Carbon::parse(now());
        // echo $data_now->year;
        // echo $data_parse;

        // エロクアントとクエリビルダの比較テスト
        // $eloqent_all = Owner::all();
        // $query_get = DB::table('owners')->select('name', 'created_at')->get();
        // $query_first = DB::table('owners')->select('name')->first();
        // $collection_test = collect([
        //     'name' => 'テスト'
        // ]);

        // var_dump($query_first);
        // dd($eloqent_all, $query_get, $query_first, $collection_test);

        $owners = Owner::select('name', 'email', 'created_at')->get();
        return view('admin.owners.index', compact('owners'));
    }


    public function create()
    {
        return view('admin.owners.create');
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

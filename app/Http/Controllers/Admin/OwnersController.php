<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Owner; // Eloquent
use Illuminate\Support\Facades\DB; // クエリビルダ
use Carbon\Carbon; // Carbonインスタンス
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules; // 独自追加:RegisterUserコントローラからRulesインスタンスのuse文を真似た

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

        $owners = Owner::select('id', 'name', 'email', 'created_at')->get();
        return view('admin.owners.index', compact('owners'));
    }


    public function create()
    {
        return view('admin.owners.create');
    }


    public function store(Request $request)
    {
        // $request->name;
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:owners'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        Owner::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()
        ->route('admin.owners.index')
        ->with('message', 'オーナー登録を実施しました。');
    }

    
    public function show($id)
    {
        //
    }

    
    public function edit($id)
    {
        $owner = Owner::findOrFail($id);
        // dd($owner);
        return view('admin.owners.edit', compact('owner'));
    }

    
    public function update(Request $request, $id)
    {
        $owner = Owner::findOrFail($id);
        $owner->name = $request->name;
        $owner->email = $request->email;
        $owner->password = Hash::make($request->name);
        $owner->save();

        return redirect()
        ->route('admin.owners.index')
        ->with('message', 'オーナー情報を更新しました。');
    }

    
    public function destroy($id)
    {
        //
    }
}

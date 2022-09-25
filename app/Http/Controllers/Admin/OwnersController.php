<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Owner; // Eloquent
use App\Models\Shop;
use Illuminate\Support\Facades\DB; // クエリビルダ
use Carbon\Carbon; // Carbonインスタンス
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules; // 独自追加:RegisterUserコントローラからRulesインスタンスのuse文を真似た
use Throwable;
use Illuminate\Support\Facades\Log;

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

        $owners = Owner::select('id', 'name', 'email', 'created_at')
        ->paginate(3);

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

        // トランザクション処理（管理者側で、オーナー作成と同時にショップも作成する）
        try{
            DB::transaction(function()use($request){
                $owner = Owner::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                Shop::create([
                    'owner_id' => $owner->id,
                    'name' => '店名を入力してください',
                    'information' => '',
                    'filename' => '',
                    'is_selling' => true
                ]);
            }, 2); // 「2」はデッドロック処理（トランザクションの最大試行回数）

        }catch(Throwable $e){
            Log::error($e);
            throw $e;
        }

        return redirect()
        ->route('admin.owners.index')
        ->with(['message' => 'オーナー登録を実施しました。',
        'status' => 'info']);
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
        ->with(['message' => 'オーナー情報を更新しました。',
        'status' => 'info']);
    }

    
    public function destroy($id)
    {
        Owner::findOrFail($id)->delete(); // ソフトデリート

        return redirect()
        ->route('admin.owners.index')
        ->with(['message' => 'オーナー情報を削除しました。',
        'status' => 'alert']);
    }

    // ソフトデリート処理で期限の切れたオーナーを保管
    public function expiredOwnerIndex(){
        $expiredOwners = Owner::onlyTrashed()->get();
        return view('admin.expired-owners',compact('expiredOwners'));
       }

    // 期限切れオーナーを完全削除
    public function expiredOwnerDestroy($id){
        Owner::onlyTrashed()->findOrFail($id)->forceDelete();
        return redirect()->route('admin.expired-owners.index');
    }
}

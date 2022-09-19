<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Image;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UploadImageRequest;
use App\Services\ImageService;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:owners');

        $this->middleware(function ($request, $next) {

            $id = $request->route()->parameter('image'); //imageのid取得
                if(!is_null($id)){
                    $imagesOwnerId = Image::findOrFail($id)->owner->id;
                    $imageId = (int)$imagesOwnerId; // キャスト 文字列→数値に型変換 

                if($imageId !== Auth::id()){
                    abort(404);
                    }
                }
                return $next($request);
        });
    }


    public function index()
    {
        $images = Image::where('owner_id', Auth::id())
        ->orderBy('updated_at', 'desc')
        ->paginate(20); // Imageテーブルと紐づくログインIDを検索

        return view('owner.images.index', compact('images'));
    }


    public function create()
    {
        return view('owner.images.create');
    }


    public function store(UploadImageRequest $request)
    {
        // dd($request);  // このようにして、storeメソッドまで渡ってきているかを確認する

        $imageFiles = $request->file('files'); // 複数の画像を取得する時の方法
        if(!is_null($imageFiles)){
            foreach($imageFiles as $imageFile){
                $fileNameToStore = ImageService::upload($imageFile, 'products'); // 第二引数prodcutはフォルダ名
                Image::create([
                    'owner_id' => Auth::id(),
                    'filename' => $fileNameToStore
                ]);
            }
        }

        return redirect()
        ->route('owner.images.index')
        ->with(['message' => '画像登録を実施しました。',
        'status' => 'info']);

    }


    public function edit($id)
    {
        $image = Image::findOrFail($id);

        return view('owner.images.edit', compact('image'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'string|max:50', 
            ]);

            $image = Image::findOrFail($id);
            $image->title = $request->title; 
            $image->save();
    
            return redirect()
            ->route('owner.images.index')
            ->with(['message' => '画像情報を更新しました。',
            'status' => 'info']);
    }

    
    public function destroy($id)
    {
        $image = Image::findOrFail($id);
        $filePath = 'public/products/'. $image->filename; 
        if(Storage::exists($filePath)){
        Storage::delete($filePath); 
        }

        Image::findOrFail($id)->delete();

        return redirect()
        ->route('owner.images.index')
        ->with(['message' => '画像を削除しました。',
        'status' => 'alert']);
    }
}

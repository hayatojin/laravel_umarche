<div>
    @if(empty($filename))
      <img src="{{ asset('images/no_image.jpg') }}">
    @else
      <img src="{{ asset('storage/shops/' . $filename) }}">
      {{-- アップロードされた画像は「strage」に保存される --}}
    @endif
</div>
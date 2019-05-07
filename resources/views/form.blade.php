<h3>お手紙運ぶマン</h3>
<h5>お手紙運ぶマン登録者からランダムなユーザーにメッセージを送信</h5>
@if (session('flash_message'))
{{ session('flash_message') }}
@endif
<form action="{{ route('formConfilm') }}" method="post">
{{ csrf_field() }}

<input type="text" name="text">

<input type="submit" value="送信">
</form>
<a href="{{ route('logout') }}">ログアウト</a>

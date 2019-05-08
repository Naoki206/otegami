<h3>お手紙運ぶマン</h3>
<h5>お手紙運ぶマン登録者からランダムなユーザーにメッセージを送信</h5>
{{ $errors->first('text') }}
@if (session('flash_message'))
{{ session('flash_message') }}
@endif
<form action="{{ route('formConfilm') }}" method="post">
{{ csrf_field() }}

<input type="text" name="text">

<input type="submit" value="送信">
</form>
<a href="{{ route('logout') }}">ログアウト</a>
<br/>
<h5>あなたが過去に送信した内容一覧</h5>
<table>
<tr>
<th>内容</th>
<th>日時</th>
</tr>
@foreach ($posts as $post)
<tr>
<td>{{ $post->text }}</td>
<td>{{ $post->created_at }}</td>
</tr>
@endforeach
</table>
<br/>
{{ $posts->links() }}

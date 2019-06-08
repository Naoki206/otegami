<h2>お手紙運ぶマン</h2>
<h4>お手紙運ぶマン登録者からランダムなユーザーにメッセージを送信</h4>
{{ $errors->first('text') }}
@if (session('flash_message'))
{{ session('flash_message') }}
@endif
<form action="{{ route('formConfilm') }}" method="post">
{{ csrf_field() }}

<input type="text" name="text">

<input type="submit" value="送信">
</form>
<a href="{{ route('receivedMessageList') }}">受け取った未返信のメッセージ一覧</a>
<br/>
<a href="{{ route('logout') }}" onclick="return confirm('ログアウトします。よろしいですか?');">ログアウト</a>
<br/>
<a href="{{ route('confilm_unsubscribe') }}">退会</a>
<br/>
<a href="{{ route('terms') }}">利用規約</a>
<br/>
<h5>あなたが過去に送信した内容一覧</h5>
@if ($posts->count())
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
@else
<h5>メッセージがありません</h5>
@endif

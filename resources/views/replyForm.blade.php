<h2>お手紙運ぶマン返信フォーム</h2>
<h4>メッセージ送り主に返信しよう!</h4>
{{ $errors->first('text') }}
<p>受け取ったメッセージ</p>
<p>「{{ $received_message }}」</p>
<h4>返信</h4>
@if (session('flash_message'))
{{ session('flash_message') }}
@endif
<form action="{{ route('ReplyformConfilm') }}" method="post">
{{ csrf_field() }}

<input type="hidden" name="reply_id" value="{{ $reply_id }}">

<input type="text" name="text">

<input type="submit" value="送信">
</form>
<h5>あなたがこのユーザーに送信した過去のメッセージ一覧</h5>
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
<h5>まだこのユーザーに返信したことがありません。</h5>
@endif

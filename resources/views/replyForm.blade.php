<h2>お手紙運ぶマン返信フォーム</h2>
<h4>メッセージ送り主に返信しよう!</h4>
{{ $errors->first('text') }}
<p>受け取ったメッセージ</p>
<p>「{{ $received_message->text }}」</p>
<h4>返信</h4>
@if (session('flash_message'))
{{ session('flash_message') }}
@endif
<form action="{{ route('ReplyformConfilm') }}" method="post">
{{ csrf_field() }}

<input type="hidden" name="reply_id" value="{{ $reply_id }}">

@if ($received_message->reply_flg)
<p>*返信済みです</p>
@endif

<input type="text" name="text">

@if ($received_message->reply_flg)
<input type="submit" value="送信" disabled>
@else
<input type="submit" value="送信">
@endif
</form>
<h4>このユーザーとのトーク履歴</h4>
@if ($posts->count())
<table>
<tr>
<th>相手からのメッセージ</th>
<th>日時</th>
<th> 自分のメッセージ</th>
<th>日時</th>
</tr>
@foreach ($posts as $post)
<tr>
@if ($first_post->user_id == $user_id && $post->from_post_id == NULL)
<td></td>
<td></td>
<td>{{ $post->text }}</td>
<td>{{ $post->created_at }}</td>
@elseif ($post->user_id == $user_id)
<td></td>
<td></td>
<td>{{ $post->text }}</td>
<td>{{ $post->created_at }}</td>
@else
<td>{{ $post->text }}</td>
<td>{{ $post->created_at }}</td>
<td></td>
<td></td>
</tr>
@endif
@endforeach
</table>
<br/>
{{ $posts->links() }}
@else
<h5>まだこのユーザーに返信したことがありません。</h5>
@endif

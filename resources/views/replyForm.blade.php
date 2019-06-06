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

<input type="text" name="text">

<input type="submit" value="送信">
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
	@if ($post->from_post_id)  
		<?php	$from_post_id = $post->from_post_id; ?>
		@foreach ($replys as $reply) 
			@if ($reply->id == $from_post_id) 
				<td>{{ $reply->text }}</td>
				<td>{{ $reply->created_at }}</td>
			@endif 
		@endforeach 
	@else
		<td></td>
		<td></td>
	@endif 
<td>{{ $post->text }}</td>
<td>{{ $post->created_at }}</td>
</tr>
@endforeach
@if (!$received_message->reply_flg)
<td>{{ $received_message->text }}</td>
<td>{{ $received_message->created_at }}</td>
@endif
</table>
<br/>
{{ $posts->links() }}
@else
<h5>まだこのユーザーに返信したことがありません。</h5>
@endif

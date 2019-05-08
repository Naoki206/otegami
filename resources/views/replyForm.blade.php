<h3>お手紙運ぶマン返信フォーム</h3>
{{ $errors->first('text') }}
@if (session('flash_message'))
{{ session('flash_message') }}
@endif
<form action="{{ route('ReplyformConfilm') }}" method="post">
{{ csrf_field() }}

<input type="hidden" name="reply_id" value="{{ $reply_id }}">

<input type="text" name="text">

<input type="submit" value="送信">
</form>
<h5>あなたが過去に送信した内容一覧</h5>
<table>
<tr>
<th>内容</th>
<th>日時</th>
</tr>
@if (isset($posts))
@foreach ($posts as $post)
<tr>
<td>{{ $post->text }}</td>
<td>{{ $post->created_at }}</td>
</tr>
@endforeach
</table>
<br/>
{{ $posts->links() }}
@endif

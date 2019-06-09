<table>
<th>未返信のメッセージ</th>
<th>受け取った日時 </th>
<th></th>
@foreach ($received_messages as $received_message)
<tr>
<td>{{ $received_message->text }}</td>
<td>{{ $received_message->created_at }}</td>
<td><a href="{{ route('Replyform', ['reply_id' => $received_message->reply_id]) }}">返信する</a></td>
</tr>
@endforeach
{{ $received_messages->links() }} 


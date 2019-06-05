@foreach ($received_messages as $received_message)
<p>{{ $received_message->text }}
@if ($received_message->reply_flg == 1)
 : 返信済み
@else
 : 未返信 <a href="{{ route('Replyform', ['reply_id' => $received_message->reply_id]) }}">返信する</a>
@endif
</p>
@endforeach
{{ $received_messages->links() }} 


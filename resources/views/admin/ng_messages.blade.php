@if (session('flash_message'))
{{ session('flash_message') }}
<br/>
@endif
@foreach ($ng_messages as $ng_message)
{{ $ng_message->message }}
<a href="{{ route('send_ng_messages', ['id' => $ng_message->id]) }}">送信</a>
<br/>
@endforeach
<br/>

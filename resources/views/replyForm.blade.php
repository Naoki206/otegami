@if (session('flash_message'))
{{ session('flash_message') }}
@endif
<form action="{{ route('ReplyformConfilm') }}" method="post">
{{ csrf_field() }}

<input type="hidden" name="reply_id" value="{{ $reply_id }}">

<input type="text" name="text">

<input type="submit" value="送信">
</form>

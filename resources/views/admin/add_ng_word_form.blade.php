<form action="{{ route('add_ng_word') }}" method="post">
{{ csrf_field() }}
<input type="text" name="word">
<input type="submit" value="追加">
</form>

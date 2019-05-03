<form action="{{ route('formConfilm') }}" method="post">
{{ csrf_field() }}

<input type="text" name="text">

<input type="submit" value="送信">
</form>

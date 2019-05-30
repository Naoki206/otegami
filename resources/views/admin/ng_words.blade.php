@foreach ($ng_words as $ng_word)
{{ $ng_word->ng_word }}
<a href="{{ route('delete_ng_word', ['id' => $ng_word->id]) }}">削除</a>
<br/>
@endforeach
<br/>
<a href="{{ route('add_ng_word_form') }}">NGワード追加</a>

<h2>お手紙運ぶマン</h2>
@if (session('flash_message'))
{{ session('flash_message') }}
<br/>
@endif
<a href="{{ url('login/twitter')}}">twitterログイン</a>
<br/>
<a href="{{ route('terms') }}">利用規約</a>

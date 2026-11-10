@extends('layouts.app') 
 
@section('title','Login') 
 
@section('content')<div class="card" style="max-width:460px;margin:auto"> 
 
<h1>Login</h1> 
<form method="POST" action="{{ route('login.store') }}">@csrf<label>Email<input class="input" type="email" name="email" value="{{ old('email') }}" required> 
</label> 
<label>Password<input class="input" type="password" name="password" required> 
</label> 
<button class="btn">Masuk</button> 
</form> 
</div>@endsection 
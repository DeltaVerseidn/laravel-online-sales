@extends('layouts.app') 
 
@section('title','Register') 
 
@section('content')<div class="card" style="max-width:460px;margin:auto"> 
 
<h1>Registrasi Pelanggan</h1> 
<form method="POST" action="{{ route('register.store') }}">@csrf<label>Nama<input class="input" name="name" value="{{ old('name') }}" required> 
</label> 
<label>Email<input class="input" type="email" name="email" value="{{ old('email') }}" required> 
</label> 
<label>Password<input class="input" type="password" name="password" required> 
</label> 
<label>Konfirmasi Password<input class="input" type="password" name="password_confirmation" required> 
</label> 
<button class="btn">Daftar</button> 
</form> 
</div>@endsection
<!DOCTYPE html> 
<html lang="id"> 
<head> 
<meta charset="UTF-8"> 
<meta name="viewport" content="width=device-width,initial-scale=1.0"> 
<title>@yield('title','Admin')</title> 
<style>*{box-sizing:border-box}body{margin:0;font-family:Arial;background:#f5f5f5}.wrap{display:grid;grid-template-columns:230px 1fr;min-height:100vh}.side{background:#161616;color:#fff;padding:24px}.side a{display:block;color:#ddd;text-decoration:none;padding:10px 0}.side h2{margin-top:0}.main{padding:28px}.card{background:#fff;border-radius:10px;padding:18px;margin-bottom:18px;border:1px solid #e5e5e5}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px}.btn{border:0;border-radius:8px;padding:9px 12px;background:#111;color:#fff;text-decoration:none;cursor:pointer}.btn-danger{background:#a91515}.input,select,textarea{width:100%;padding:9px;border:1px solid #ccc;border-radius:7px;margin-top:5px}label{display:block;margin-bottom:12px}table{width:100%;border-collapse:collapse}th,td{padding:10px;border-bottom:1px solid #eee;text-align:left}.alert{padding:11px;background:#e8f7ee;margin-bottom:15px;border-radius:8px}.error{background:#fdeaea}.row{display:flex;gap:10px;flex-wrap:wrap;align-items:end}.muted{color:#666}</style> 
</head> 
<body> 
<div class="wrap"> 
<aside class="side"> 
<h2>Admin Panel</h2> 
<a href="{{ route('admin.dashboard') }}">Dashboard</a> 
<a href="{{ route('admin.categories.index') }}">Kategori</a> 
<a href="{{ route('admin.products.index') }}">Produk</a> 
<a href="{{ route('admin.stock.index') }}">Stok & Barang Masuk</a> 
<a href="{{ route('admin.shipping.index') }}">Ekspedisi</a> 
<a href="{{ route('admin.orders.index') }}">Transaksi</a> 
<a href="{{ route('shop.index') }}">Lihat Toko</a> 
<form method="POST" action="{{ route('logout') }}">@csrf<button class="btn" type="submit">Logout</button> 
</form> 
</aside> 
<main class="main"> 
@if(session('success'))<div class="alert">{{ session('success') }}</div>@endif @if(session('error'))<div class="alert error">{{ session('error') }}</div>@endif @if($errors->any())<div class="alert error"> 
 
<ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul> 
 
</div>@endif 
 
@yield('content')</main> 
</div> 
</body> 
</html>
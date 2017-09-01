@extends('scaffold-interface.layouts.app')
@section('title','Show')
@section('content')

<section class="content">
    <h1>
        Show email
    </h1>
    <br>
    <a href='{!!url("email")!!}' class = 'btn btn-primary'><i class="fa fa-home"></i>Email Index</a>
    <hr>
    <div>
        {!!$email->body!!}
    </div>
</section>
@endsection
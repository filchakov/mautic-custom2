@extends('scaffold-interface.layouts.app')
@section('title','Edit')
@section('content')

<section class="content">
    <h1>
        Edit email
    </h1>
    <a href="{!!url('email')!!}" class = 'btn btn-primary'><i class="fa fa-home"></i> Email Index</a>
    <br>
    <form method = 'POST' action = '{!! url("email")!!}/{!!$email->
        id!!}/update'> 
        <input type = 'hidden' name = '_token' value = '{{Session::token()}}'>
        <div class="form-group">
            <label for="title">title</label>
            <input id="title" name = "title" type="text" class="form-control" value="{!!$email->
            title!!}"> 
        </div>
        <div class="form-group">
            <label for="body">body</label>
            <input id="body" name = "body" type="text" class="form-control" value="{!!$email->
            body!!}"> 
        </div>
        <div class="form-group">
            <label for="mautic_email_id">mautic_email_id</label>
            <input id="mautic_email_id" name = "mautic_email_id" type="text" class="form-control" value="{!!$email->
            mautic_email_id!!}"> 
        </div>
        <div class="form-group">
            <label for="project_id">project_id</label>
            <input id="project_id" name = "project_id" type="text" class="form-control" value="{!!$email->
            project_id!!}"> 
        </div>
        <button class = 'btn btn-success' type ='submit'><i class="fa fa-floppy-o"></i> Update</button>
    </form>
</section>
@endsection
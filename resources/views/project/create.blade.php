@extends('scaffold-interface.layouts.app')
@section('title','Create')
@section('content')

<section class="content">
    <h1>
        Create project
    </h1>
    <a href="{!!url('project')!!}" class = 'btn btn-danger'><i class="fa fa-home"></i> Project Index</a>
    <hr>
    <form method = 'POST' action = '{!!url("project")!!}' enctype="multipart/form-data">
        <input type = 'hidden' name = '_token' value = '{{Session::token()}}'>
        <div class="form-group">
            <label for="url">url</label>
            <input id="url" name = "url" type="text" class="form-control">
        </div>

        <div class="form-group">
            <label for="logo">Logo</label>
            <input type="file" name="logo" type="text" class="form-control" value="" accept="image/jpeg,image/png" required>
        </div>

        <button class = 'btn btn-success' type ='submit'> <i class="fa fa-floppy-o"></i> Save</button>
    </form>
</section>
@endsection
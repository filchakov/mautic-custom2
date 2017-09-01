@extends('scaffold-interface.layouts.app')
@section('title','Edit')
@section('content')

<section class="content">
    <h1>
        Edit project
    </h1>
    <a href="{!!url('project')!!}" class = 'btn btn-primary'><i class="fa fa-home"></i> Project Index</a>
    <br>
    <form method = 'POST' action = '{!! url("project")!!}/{!!$project->id!!}/update' enctype="multipart/form-data">
        <input type = 'hidden' name = '_token' value = '{{Session::token()}}'>
        <div class="form-group">
            <label for="url">URL</label>
            <input id="url" name="url" type="text" class="form-control" value="{!!$project->url!!}">
        </div>

        <div class="form-group">
            <label for="logo">Logo</label>
            <input type="file" name="logo" type="text" class="form-control" value="{!!$project->url!!}" accept="image/jpeg,image/png">
        </div>
        <button class = 'btn btn-success' type ='submit'><i class="fa fa-floppy-o"></i> Update</button>
    </form>
</section>
@endsection
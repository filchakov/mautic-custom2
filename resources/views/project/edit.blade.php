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
            <label for="from_name">From name</label>
            <input id="from_name" name="from_name" type="text" required="required" class="form-control" value="{!!$project->from_name!!}">
        </div>
        <div class="form-group">
            <label for="last_name">Last name</label>
            <input id="last_name" name="last_name" type="text" required="required" class="form-control" value="{!!$project->last_name!!}">
        </div>
        <div class="form-group">
            <label for="from_email">From email</label>
            <input id="from_email" name="from_email" type="email" required="required" class="form-control" value="{!!$project->from_email!!}">
        </div>
        <div class="form-group">
            <label for="relpy_to">Reply to</label>
            <input id="relpy_to" name="relpy_to" type="email" required="required" class="form-control" value="{!!$project->relpy_to!!}">
        </div>

        <div class="form-group">
            <label for="logo">Logo</label>
            <input type="file" name="logo" type="text" class="form-control" value="{!!$project->logo!!}" accept="image/jpeg,image/png">
            <img src="/{!!$project->logo!!}" />
        </div>
        <hr/>
        <button class = 'btn btn-success' type ='submit'><i class="fa fa-floppy-o"></i> Update</button>
    </form>
</section>
@endsection
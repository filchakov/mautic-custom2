@extends('scaffold-interface.layouts.app')
@section('title','Create')
@section('content')

<section class="content">
    <h1>
        Create email
    </h1>
    <a href="{!!url('email')!!}" class = 'btn btn-danger'><i class="fa fa-home"></i> All emails</a>
    <hr>
    <form method = 'POST' action = '{!!url("email")!!}'>
        <input type = 'hidden' name = '_token' value = '{{Session::token()}}'>
        <div class="form-group">
            <label for="title">Email subject</label>
            <input id="title" name = "title" type="text" class="form-control" />
        </div>

        <div class="form-group">
            <label for="project_id">Email for project</label>
            {!!Form::select('project_id', \App\Project::pluck('url', 'id')->toArray(), 1, ['class' => 'form-control', 'disabled' => 'disabled'])!!}
        </div>

        <input type="hidden" name="mautic_email_id" value="0" />
        <input type="hidden" name="body" value="empty" />
        <input type="hidden" name="project_id" value="1" />

        <button class='btn btn-success' type='submit'> <i class="fa fa-floppy-o"></i> Next step</button>
    </form>
</section>
@endsection
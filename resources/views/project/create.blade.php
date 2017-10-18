@extends('scaffold-interface.layouts.app')
@section('title','Create')
@section('content')

<section class="content">
    <h1>
        Create project
    </h1>
    <a href="{!!url('project')!!}" class='btn btn-danger'><i class="fa fa-home"></i> Project Index</a>
    <hr>
    <form method='POST' action='{!!url("project")!!}' enctype="multipart/form-data">
        <input type='hidden' name='_token' value='{{Session::token()}}'>
        <div class="form-group">
            <label for="url">URL (for example <i>https://test.com</i> or <i>http://test.com</i>)</label>
            <input id="url" name="url" type="url" pattern="^(https?://)?([a-zA-Z0-9]([a-zA-ZäöüÄÖÜ0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$" required="required" class="form-control">
        </div>

        <div class="form-group">
            <label for="company_name">Company name</label>
            <input id="company_name" name="company_name" type="text" required="required" class="form-control" value="">
        </div>

        <div class="form-group">
            <label for="from_name">From name</label>
            <input id="from_name" name="from_name" type="text" required="required" class="form-control">
        </div>
        <div class="form-group">
            <label for="last_name">Last name</label>
            <input id="last_name" name="last_name" type="text" required="required" class="form-control">
        </div>
        <div class="form-group">
            <label for="from_email">From email</label>
            <input id="from_email" name="from_email" type="email" required="required" class="form-control">
        </div>
        <div class="form-group">
            <label for="relpy_to">Reply to</label>
            <input id="relpy_to" name="relpy_to" type="email" required="required" class="form-control">
        </div>

        <div class="form-group">
            <label for="mautic_segment_id">Segment ID on <a href="{{env('MAUTIC_URL')}}/s/segments" target="_blank">Mautic</a></label>
            <input id="mautic_segment_id" name="mautic_segment_id" type="number" required="required" class="form-control">
        </div>

        <div class="form-group">
            <label for="logo">Logo</label>
            <input type="file" name="logo" type="text" class="form-control" value="" accept="image/jpeg,image/png" required>
        </div>

        <button class='btn btn-success' type ='submit'> <i class="fa fa-floppy-o"></i> Save</button>
    </form>
</section>
@endsection
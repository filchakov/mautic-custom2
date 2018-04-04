@extends('scaffold-interface.layouts.app')
@section('title','Edit')
@section('content')

    <section class="content">
        <h2>
            Edit email "{{$email->title}}", ({{$project->url}})
        </h2>
        <a href="{!!url('email')!!}" class = 'btn btn-primary'><i class="fa fa-home"></i> Email Index</a>
        <br>

        <hr/>
        <form method="POST" action="{{route('email.update', ['id' => $email->id])}}" enctype="multipart/form-data">
            {{csrf_field()}}

            <input type="hidden" name="source_type" value="html">
            <input type="hidden" name="main_template_email_id" value="{{$email->parent_email_id==0? $email->id : $email->parent_email_id}}">

            <div class="form-row text-center">
                <div class="col-lg-3"></div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="subject_name">Subject</label>
                        <input type="text" name="name" class="form-control" placeholder="Subject for email" value="{{$email->title}}" required="required"/>
                    </div>

                    <label class="btn btn-default btn-file">
                        Add HTML file <input type="file" name="source_template" accept="text/html" required="required">
                    </label>

                    <input type="submit" class="btn btn-success pull-right" />
                </div>
                <div class="col-lg-3"></div>
            </div>
        </form>

    </section>
@endsection
@extends('scaffold-interface.layouts.app')
@section('title', $title)
@section('content')

    <section class="content">
        <h1>
            Create segments
        </h1>

        <a href="{!!url('segment')!!}" class='btn btn-danger'><i class="fa fa-home"></i> Segment Index</a>

        <hr>

        <form method='POST' action='{!!route("segment.store")!!}' enctype="multipart/form-data">

            {{csrf_field()}}

            <blockquote>
                <b>Attempt!</b> This segment will be created for each project. You have to add additional filters for this segment (except for field "project", this field will fill for all segments).
            </blockquote>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-group">
                <label for="name">Name</label>
                <input id="name" name="name" type="text" required="required" class="form-control" placeholder="Segment name">
            </div>

            <div class="form-group">
                <label for="">Tags</label>
                <div class="col-md-12">
                    @foreach($tags as $tag_id => $tag_name)
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="tags[{{$tag_id}}]" id="email_{{$tag_id}}">
                                <label for="email_{{$tag_id}}"> {{$tag_name}}</label>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            <button class='btn btn-success' type ='submit'> <i class="fa fa-floppy-o"></i> Save</button>
        </form>
    </section>
@endsection
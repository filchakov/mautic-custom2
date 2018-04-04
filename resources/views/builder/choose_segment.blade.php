@extends('scaffold-interface.layouts.app')
@section('title','Please choose projects')
@section('content')

    <section class="content">

        <h1>Сhoose segment for campaign</h1>

        <form method="GET" id="form_projects" action="{{route('email.create')}}">

            @foreach(\request()->get('projects', []) as $project)
                <input type="hidden" name="projects[]" value="{{$project}}"/>
            @endforeach


            <div class="form-group">
                @foreach($segments as $segment)
                    <div class="radio">
                        <label>
                            <input type="radio" name="segment_id" value="{{$segment->id}}" required="required">
                            <b>{{$segment->name}}</b>, <i>(Tags: {{implode(', ', $segment->tags)}})</i>
                        </label>
                    </div>
                @endforeach
            </div>

            <button class="btn btn-success btn-lg">Next</button>

        </form>

    </section>

@endsection

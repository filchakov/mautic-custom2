@extends('scaffold-interface.layouts.app')
@section('title','Index')
@section('content')

<section class="content">
    <h1>Segments</h1>
    <a href='{{route("segment.create")}}' class='btn btn-success'><i class="fa fa-plus"></i> New</a>

    <br/>
    <br/>

    <table class = "table table-striped table-bordered table-hover" style = 'background:#fff'>
        <thead>
            <th style="width:5%">ID</th>
            <th style="width:90%">Segment name</th>
            <th style="width:5%">Actions</th>
        </thead>
        <tbody>
        @foreach($segments as $segment)
            <tr>
                <td>{{$segment->id}}</td>
                <td>
                    <a href="{{route('segment.setting', ['id' => $segment->id])}}" class="btn btn-secondary">
                        {!!$segment->name!!}
                    </a>
                    <p>
                        <b>Tags: </b><i>{{implode(', ', $segment->tags)}}</i>
                    </p>
                </td>
                <td>
                    <a href="{{route('segment.projects', ['id' => $segment->id])}}" class="btn btn-primary btn-secondary">
                        <i class='fa fa-reorder'> Projects</i>
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {!! $segments->render() !!}


</section>
@endsection
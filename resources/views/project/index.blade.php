@extends('scaffold-interface.layouts.app')
@section('title','Index')
@section('content')

<section class="content">
    <h1>
        Projects
    </h1>
    <a href='{!!url("project")!!}/create' class = 'btn btn-success'><i class="fa fa-plus"></i> New</a>
    <br>
    <br>
    <table class = "table table-striped table-bordered table-hover" style = 'background:#fff'>
        <thead>
            <th width="10">ID</th>
            <th width="100">Logo</th>
            <th>URL</th>
            <th>From name</th>
            <th>From email</th>
            <th>Reply to</th>
            <th>Bounced emails</th>
            <th>Segments</th>
            <th>Actions</th>
        </thead>
        <tbody>
            @foreach($projects as $project) 
            <tr>
                <td>{{$project->id}}</td>
                <td><img src="{!!$project->logo!!}?time={{time()}}" style="max-width: 100px;"/></td>
                <td>{!!$project->url!!}</td>
                <td>{!!$project->from_name!!} {!! $project->last_name !!}</td>
                <td>{!!$project->from_email!!}</td>
                <td>{!!$project->relpy_to!!}</td>
                <td class="text-center">
                    @if(!empty($project->bounced_emails))
                        <a href="{{route('email.bounced', ['id' => $project->id])}}" class="btn btn-info">
                            <i class="fa fa-download"> </i> {!!$project->bounced_emails!!}
                        </a>
                    @else
                        {!!$project->bounced_emails!!}
                    @endif

                </td>
                <td>
                    @if($project->segments_counter['total'] > $project->segments_counter['created'])
                        <p>This project need {{$project->segments_counter['total'] - $project->segments_counter['created']}} segment(s)</p>
                        @else
                        <p>Project have all segments</p>
                    @endif
                </td>
                <td>
                    <a data-toggle="modal" data-target="#myModal" class = 'delete btn btn-danger btn-xs' data-link = "/project/{!!$project->id!!}/deleteMsg" ><i class = 'fa fa-trash'> delete</i></a>
                    <a href = '#' class = 'viewEdit btn btn-primary btn-xs' data-link = '/project/{!!$project->id!!}/edit'><i class = 'fa fa-edit'> edit</i></a>
                </td>
            </tr>
            @endforeach 
        </tbody>
    </table>
    {!! $projects->render() !!}

</section>
@endsection
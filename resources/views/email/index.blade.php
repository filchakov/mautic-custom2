@extends('scaffold-interface.layouts.app')
@section('title','Index')
@section('content')

<section class="content">
    <h1>
        Emails
    </h1>
    <a href='{!!url("email")!!}/create' class = 'btn btn-success'><i class="fa fa-plus"></i> New</a>
    <br>
    <br>
    <table class = "table table-striped table-bordered table-hover" style = 'background:#fff'>
        <thead>
            <th width="50px">#ID</th>
            <th>Title</th>
            <th width="400px">Actions</th>
        </thead>
        <tbody>
            @foreach($emails as $email) 
            <tr>
                <td>{!!$email->id!!}</td>
                <td>{!!$email->title!!}</td>
                <td>
                    <a href="{{route('email.customize', ['id' => $email->id])}}" class = 'btn btn-primary'>
                        <i class = 'fa fa-reorder'></i> Customize for projects
                    </a>
                    {{--<a href="{{route('email.builder', ['email_id' => $email->id, 'project_id' => $email->project_id])}}" class = 'btn btn-primary'>
                        <i class = 'fa fa-edit'></i> Edit
                    </a>--}}
                    <a href="{{route('email.show', ['id' => $email->id])}}" target="_blank" class = 'btn btn-warning'>
                        <i class = 'fa fa-eye'> Show</i>
                    </a>
                </td>
            </tr>
            @endforeach 
        </tbody>
    </table>
    {!! $emails->render() !!}

</section>
@endsection
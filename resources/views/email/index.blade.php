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
            <th width="250px">Actions</th>
        </thead>
        <tbody>
            @foreach($emails as $email) 
            <tr>
                <td>{!!$email->id!!}</td>
                <td>{!!$email->title!!}</td>
                <td>
                    <div class="btn-group">
                        <a href="{{route('email.customize', ['id' => $email->id])}}" class = 'btn btn-primary'>
                            <i class = 'fa fa-reorder'></i> Details
                        </a>
                        <a href="{{route('email.show', ['id' => $email->id])}}" target="_blank" class = 'btn btn-warning'>
                            <i class = 'fa fa-eye'> Show</i>
                        </a>
                        <a onclick="if(confirm('You are sure, that want to remove #{{$email->id}}?')){ window.location.href = '{{route('email.delete', ['id' => $email->id])}}'; } " target="_blank" class = 'btn btn-danger'>
                            <i class = 'fa fa-remove'> Delete</i>
                        </a>
                    </div>
                </td>
            </tr>
            @endforeach 
        </tbody>
    </table>
    {!! $emails->render() !!}

</section>
@endsection
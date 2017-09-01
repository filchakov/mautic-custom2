@extends('scaffold-interface.layouts.app')
@section('title','Show')
@section('content')

<section class="content">
    <h1>
        Show project
    </h1>
    <br>
    <a href='{!!url("project")!!}' class = 'btn btn-primary'><i class="fa fa-home"></i>Project Index</a>
    <br>
    <table class = 'table table-bordered'>
        <thead>
            <th>Key</th>
            <th>Value</th>
        </thead>
        <tbody>
            <tr>
                <td> <b>url</b> </td>
                <td>{!!$project->url!!}</td>
            </tr>
        </tbody>
    </table>
</section>
@endsection
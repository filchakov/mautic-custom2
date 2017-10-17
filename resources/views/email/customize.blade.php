@extends('scaffold-interface.layouts.app')
@section('title','Index')
@section('content')

<section class="content">
    <h1>
        {{$title}}
    </h1>
    <a href="{!!url('email')!!}" class = 'btn btn-primary'><i class="fa fa-home"></i> Emails</a>
    <hr/>
    <table class = "table table-striped table-bordered table-hover" style = 'background:#fff'>
        <thead>
            <th width="50px">#ID</th>
            <th width="200px">Logo</th>
            <th>URL</th>
            <th></th>
            <th width="400px">Actions</th>
        </thead>
        <tbody>
            @foreach($projects as $project)
            <tr>
                <td>{!!$project->id!!}</td>
                <td>
                    <img height="30px" src="/{!!$project->logo!!}" />
                </td>
                <td>{!!$project->url!!}</td>
                <td style="text-align: center;">
                    @if($id == $project->emails->id)
                        Main template
                    @elseif($project->emails->json_elements == \App\Email::find($id)->json_elements)
                        Template as the main
                    @else
                        Unique template for {{$project->url}}
                    @endif
                </td>
                <td>

                    @if(is_null($project->emails))
                        <a href="{{route('email.builder', ['email_id' => -1, 'project_id' => $project->id, 'main_template_email' => $main_template_email])}}" class = 'btn btn-primary'>
                            <i class = 'fa fa-edit'></i> Create
                        </a>
                    @else
                        <a href="{{route('email.builder', ['email_id' => $project->emails->id, 'project_id' => $project->id])}}" class = 'btn btn-primary'>
                            <i class = 'fa fa-edit'></i> Edit
                        </a>
                        <a href="{{route('email.show', ['id' => $project->emails->id])}}" target="_blank" class = 'btn btn-warning'>
                            <i class = 'fa fa-eye'> Show template</i>
                        </a>
                    @endif
                    <a class="btn btn-primary" target="_blank" href="{{env('MAUTIC_URL')}}/email/preview/{{$project->emails->mautic_email_id}}">
                        <i class="fa fa-external-link"> Check email on mautic</i>
                    </a>

                </td>
            </tr>
            @endforeach 
        </tbody>
    </table>

</section>
@endsection
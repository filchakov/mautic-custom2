@extends('scaffold-interface.layouts.app')
@section('title','Edit')
@section('content')

<section class="content">
    <h1>
        Edit project
    </h1>
    <a href="{!!url('project')!!}" class = 'btn btn-primary'><i class="fa fa-home"></i> Project Index</a>

    <br>

    @if($id != 1 && $emails->count() > 0)
        <h1>
            We already have templates in our database
        </h1>

        <h2 class="text-center">Do you want to add these emails for project?</h2>

        <hr/>

        <form method="POST" action="{{route('project.copy_emails')}}">

            {{csrf_field()}}

            <p>
                <a href="#" onclick="$('[type=checkbox]').trigger('click');">Select/Unselect all</a>
            </p>
            <table width="100%" class="table table-hover">

                <input type="hidden" name="projects[]" value="{{$id}}" />

                @foreach($emails as $email)

                    <tr>
                        <td width="1%">
                            <input type="checkbox" checked="checked" name="email[{{$email->id}}]" id="email_{{$email->id}}">
                        </td>
                        <td width="89%">
                            <label for="email_{{$email->id}}"> {{$email->title}}</label>
                        </td>
                        <td width="5%">
                            <a href="{{env('MAUTIC_URL')}}/email/preview/{{$email->mautic_email_id}}" target="_blank" class="badge">
                                Open on Mautic
                            </a>
                        </td>
                        <td width="5%">
                            <a href="{{route('email.show', ['id' => $email->id])}}" class="badge" target="_blank">
                                Show template #{{$email->id}}
                            </a>
                        </td>
                    </tr>
                @endforeach
            </table>

            <p>
                <a href="#" onclick="$('[type=checkbox]').trigger('click');">Select/Unselect all</a>
            </p>

            <input type="submit" name="submit" value="Yes. Do it" class="btn btn-success btn-lg"/>
            <a href="{{route('project.index')}}" class="btn btn-danger btn-lg">No</a>

        </form>

        <hr/>
    @endif




    <form method = 'POST' action = '{!! url("project")!!}/{!!$project->id!!}/update' enctype="multipart/form-data">
        <input type = 'hidden' name = '_token' value = '{{Session::token()}}'>
        <div class="form-group">
            <label for="url">URL</label>
            <input id="url" name="url" type="text" class="form-control" value="{!!$project->url!!}">
        </div>

        <div class="form-group">
            <label for="company_name">Company name</label>
            <input id="company_name" name="company_name" type="text" required="required" class="form-control" value="{!!$project->company_name!!}">
        </div>

        <div class="form-group">
            <label for="from_name">From name</label>
            <input id="from_name" name="from_name" type="text" required="required" class="form-control" value="{!!$project->from_name!!}">
        </div>
        <div class="form-group">
            <label for="last_name">Last name</label>
            <input id="last_name" name="last_name" type="text" required="required" class="form-control" value="{!!$project->last_name!!}">
        </div>
        <div class="form-group">
            <label for="from_email">From email</label>
            <input id="from_email" name="from_email" type="email" required="required" class="form-control" value="{!!$project->from_email!!}">
        </div>
        <div class="form-group">
            <label for="relpy_to">Reply to</label>
            <input id="relpy_to" name="relpy_to" type="email" required="required" class="form-control" value="{!!$project->relpy_to!!}">
        </div>

        <div class="form-group">
            <label for="mautic_segment_id">Segment ID on <a href="{{env('MAUTIC_URL')}}/s/segments" target="_blank">Mautic</a></label>
            <input id="mautic_segment_id" name="mautic_segment_id" type="number" required="required" class="form-control" value="{!!$project->mautic_segment_id!!}">
        </div>

        <div class="form-group">
            <label for="logo">Logo</label>
            <input type="file" name="logo" type="text" class="form-control" value="{!!$project->logo!!}" accept="image/jpeg,image/png">
            <img src="/{!!$project->logo!!}" />
        </div>
        <hr/>
        <button class = 'btn btn-success' type ='submit'><i class="fa fa-floppy-o"></i> Update</button>
    </form>
</section>
@endsection
@extends('scaffold-interface.layouts.app')
@section('title','Edit')
@section('content')

    <section class="content">
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

    </section>
@endsection
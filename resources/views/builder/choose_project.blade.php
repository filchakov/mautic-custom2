@extends('scaffold-interface.layouts.app')
@section('title','Please choose projects')
@section('content')

    <section class="content">
        <h1>
            Сhoose projects for which you want to create a new email
        </h1>

        <form method="GET" id="form_projects" action="{{route('email.create')}}">

            <table class="table table-striped table-bordered table-hover" style="background:#fff">
                <thead>
                    <tr>
                        <td width="20px"></td>
                        <td width="70px">Logo</td>
                        <td>Name</td>
                    </tr>
                </thead>
                <tbody>
                    <input type="hidden" name="projects[]" value="1" \>

                    @foreach($projects as $project)
                        <tr>
                            <td>
                                <input type="checkbox" name="projects[]" value="{{$project->id}}" checked="checked" id="checkbox{{$project->id}}" @if($project->id == 1) disabled="disabled"@endif />
                            </td>
                            <td>
                                <label for="checkbox{{$project->id}}">
                                    <img src="/{{$project->logo}}?time={{time()}}" height="20px"/>
                                </label>
                            </td>
                            <td>
                                <label for="checkbox{{$project->id}}">
                                    {{$project->url}} ({{$project->company_name}})
                                </label>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <p>
                <a href="#" onclick="$('[type=checkbox]').trigger('click');">Select/Unselect all projects</a>
            </p>

            <button class="btn btn-success btn-lg" id="js_form_projects">Next</button>

        </form>

    </section>

    <script type="text/javascript">
        $('#js_form_projects').click(function (e) {

            if($('#form_projects [type=checkbox]:checked').length == 0){
                alert('Please choose at least one project');
                return false;
            }

        });
    </script>
@endsection

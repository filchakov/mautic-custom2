@extends('scaffold-interface.layouts.app')
@section('title','Index')
@section('content')

    <section class="content">
        <h1>Segment "{{$segment->name}}" for projects</h1>

        <a href='{{route("segment.index")}}' class='btn btn-success'><i class="fa fa-reorder"></i> Back</a>

        <br/>
        <br/>

        <table class = "table table-striped table-bordered table-hover" style = 'background:#fff'>
            <thead>
            <th style="width:5%">Logo</th>
            <th style="width:84%">URL</th>
            <th style="width:10%">Count leads</th>
            </thead>
            <tbody>

            @foreach($all_projects as $project)
                <tr>
                    <td>
                        <img src="/{!!$project->logo!!}?time={{time()}}" style="max-width: 100px;"/>
                    </td>
                    <td>
                        {!!$project->url!!}
                    </td>
                    <td style="text-align: center;">
                        @if(in_array($project->id, $segment_to_projects->pluck('project_id')->toArray()))
                            <a href="{{env('MAUTIC_URL', '')}}/s/contacts?search={{urlencode('segment:' . $segment_to_projects->where('project_id', $project->id)->first()->segment_alias)}}" target="_blank">
                                <div data-project_id="{{$project->id}}" data-segment_id="{{$segment_to_projects->first()->id}}">
                                    <b></b>
                                    <div class="loader"></div>
                                </div>
                            </a>

                            <script type="application/javascript">
                                $.get('/segment/count_leads?segment_id={{$segment_to_projects->where('project_id', $project->id)->first()->segment_id}}&project_id={{$project->id}}', function (data) {
                                    $('[data-project_id={{$project->id}}] .loader').hide();
                                    $('[data-project_id={{$project->id}}] b').text(data.count);
                                });
                            </script>
                        @else
                            <a href="{{route('segment.create_for_project', ['id' => $segment->id, 'project_id' => $project->id])}}" class="btn btn-success">
                                <i class="fa fa-plus"> </i> Create segment
                            </a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>


        <style type="text/css">
            .loader {
                border: 8px solid #f3f3f3; /* Light grey */
                border-top: 8px solid #3498db; /* Blue */
                border-radius: 50%;
                width: 20px;
                height: 20px;
                margin: auto;
                animation: spin 2s linear infinite;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>


    </section>
@endsection
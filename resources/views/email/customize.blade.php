@extends('scaffold-interface.layouts.app')
@section('title','Index')
@section('content')

<section class="content">
    <h1>
        {{$title}} ({{$parent_email->title}})
    </h1>
    <a href="{!!url('email')!!}" class = 'btn btn-primary'><i class="fa fa-home"></i> Emails</a>
    <hr/>
    <table class = "table table-striped table-bordered table-hover" style = 'background:#fff'>
        <thead>
            <th width="50px">#ID</th>
            <th width="200px">Logo</th>
            <th width="100px">URL</th>
            <th width="170px"></th>
            <th>Stats</th>
            <th width="200px">Actions</th>
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
                    <div data-email_id="{{$project->emails->id}}">
                        <span class="btn btn-success btn-lg btn-block js_stats_btn js_stats_btn_{{$project->emails->id}}"
                              data-project-id="{{$project->emails->id}}"
                        >
                            Get stats
                        </span>
                        <table class="table js_stats_{{$project->emails->id}}_table" style="display: none;">
                            <tr>
                                <td width="50px">
                                    Subject:
                                </td>
                                <td>
                                    <div class="loader"></div>
                                    <b class="js_subject"></b>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Read:
                                </td>
                                <td>
                                    <div class="loader"></div>
                                    <b class="js_read"></b>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Sent:
                                </td>
                                <td>
                                    <div class="loader"></div>
                                    <b class="js_sent"></b>
                                </td>
                            </tr>
                            <tr style="text-align: center;">
                                <td colspan="2">
                                    <div class="btn-group">
                                        <a href="https://m.hiretrail.com/s/emails/view/{{$project->emails->mautic_email_id}}" target="_blank" class="btn btn-success disabled">Report of clicks</a>
                                        <a href="{{route('email.test_campaign', ['id' => $project->emails->id])}}" target="_blank" class="btn btn-warning disabled">Create test campaign</a>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <script type="application/javascript">

                        function get_stats(email_id, callback) {
                            $.get('/email/' + email_id + '/stats', function (data) {

                                var percents = 0;

                                if(data.read > 0){
                                    percents = Math.round(data.read/(data.sent/100));
                                }

                                $('[data-email_id=' + email_id + '] .loader').hide();
                                $('[data-email_id=' + email_id + '] .js_read').text(data.read + ' (' + percents + '%)');
                                $('[data-email_id=' + email_id + '] .js_subject').text(data.subject);
                                $('[data-email_id=' + email_id + '] .js_sent').text(data.sent);
                                $('[data-email_id=' + email_id + '] .disabled').removeClass('disabled');

                            }).fail(function() {
                                $('[data-email_id=' + email_id + '] .loader').hide();
                                $('[data-email_id=' + email_id + '] .js_read').text('N/\A');
                                $('[data-email_id=' + email_id + '] .js_sent').text('N/\A');
                                $('[data-email_id=' + email_id + '] .js_subject').text('Empty');
                                setInterval(8000, get_stats(email_id, function () {
                                    window.location.reload();
                                }));
                            });

                            callback();
                        }

                        $('.js_stats_btn').hover(function () {
                            get_stats($(this).attr('data-project-id'), function () {});
                        });

                        $('.js_stats_btn').click(function () {
                            $('.js_stats_btn_' + $(this).attr('data-project-id')).hide();
                            $('.js_stats_' + $(this).attr('data-project-id') + '_table').show();
                        });

                    </script>

                </td>
                <td>

                    <div class="btn-group-vertical">
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
                        <a class="btn btn-danger" onclick="if(confirm('You are sure, that want to remove #{{$project->emails->id}}?')){ window.location.href = '{{route('email.delete', ['id' => $project->emails->id])}}'; } ">
                            <i class="fa fa-remove"> Delete</i>
                        </a>
                    </div>

                </td>
            </tr>
            @endforeach 
        </tbody>
    </table>

    @if($projects_without_email->count() > 0)

        <hr/>
        <h2>These projects don't have this email. If you want to create template for these emails, please click on green button</h2>
        <form method="POST" action="{{route('project.copy_emails')}}">
            {{csrf_field()}}
            <p>
                <a href="#" onclick="$('[type=checkbox]').trigger('click');">Select/Unselect all</a>
            </p>

            <table width="100%" class="table table-hover">
                <input type="hidden" name="email[{{$id}}]" value="{{$id}}"/>
                @foreach($projects_without_email as $project)
                    <tr>
                        <td width="1%">
                            <input type="checkbox" checked="checked" name="projects[{{$project->id}}]" value="{{$project->id}}" id="projects_{{$project->id}}">
                        </td>
                        <td width="89%">
                            <label for="projects_{{$project->id}}"> {{$project->url}}</label>
                        </td>
                        <td width="5%">
                            <a href="{{route('project.edit', ['id' => $project->id])}}" class="badge" target="_blank">
                                Setting
                            </a>
                        </td>
                    </tr>
                @endforeach
            </table>
            <input type="submit" name="submit" value="Yes. Do it" class="btn btn-success btn-lg"/>
        </form>

    @endif

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
@extends('scaffold-interface.layouts.app')
@section('title', $title)
@section('content')

    <section class="content">
        <h1>
            Tests for campaign "{{$title}}"
        </h1>
        <a href="{!!route('email.customize',['id' => empty($emails->first()->parent_email_id)? $emails->first()->id : $emails->first()->parent_email_id])!!}" class = 'btn btn-primary'><i class="fa fa-home"></i> Emails</a>
        <hr/>

        <table class="table table-striped table-bordered table-hover">
            <thead>
                <td>URL</td>
                <td>Logo</td>
                <td>Check links</td>
            </thead>
            @foreach($emails as $email)
                <tr>
                    <td style="text-align: center">

                        <img src="{{'https://email-builder.hiretrail.com/' . $email->project()->first()->logo}}" width="200px"/>
                        <hr/>
                        <p>{{$email->project()->first()->url}}</p>

                        <hr/>
                        <a href="https://m.hiretrail.com/email/preview/{{$email->mautic_email_id}}" target="_blank">
                            <img src="https://m.hiretrail.com/media/images/favicon.ico" width="25px"/> Open template on Mautic
                        </a>
                        <hr/>
                        <a href="{{route('email.builder', ['email_id' => $email->id, 'project_id' => $email->project_id])}}" class="btn btn-primary">
                            <i class="fa fa-edit"></i> Edit template
                        </a>
                    </td>

                    @if($logo[$email->id])
                        <td style="background-color: green">
                            logo found in email
                        </td>
                    @else
                        <td style="background-color: red">
                            logo not found
                        </td>
                    @endif

                    <td>
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <td>URL</td>
                                <td>Text for link</td>
                                <td>Title on page</td>
                            </thead>
                            @foreach($links[$email->id] as $url => $link)

                            @if($link['status'])
                               <tr style="background-color: green" class="js-row-{{md5($url)}}">
                            @elseif(is_null($link['status']))
                               <tr style="background-color: orange" class="js-row-{{md5($url)}}">
                            @else
                               <tr style="background-color: red" class="js-row-{{md5($url)}}">
                            @endif
                                    <td>
                                        <a href="{{$url}}" target="_blank"
                                           @if(is_null($link['status']) && !substr_count($url, '{'))
                                            url="{{$url}}"
                                           @endif
                                           class="btn btn-xs btn-success js-link-{{md5($url)}}">{{$url}}</a>
                                    </td>
                                    <td class="js-text-{{md5($url)}}">
                                        {!! $link['text'] !!}
                                    </td>
                                    <td class="js-title-{{md5($url)}}">
                                        {!! $link['title_page'] !!}

                                        <script>
                                            $(document).ready(function() {
                                                $.ajax({
                                                    method: "POST",
                                                    url: "{{route('email.urlchecking')}}",
                                                    data: { url: "{{$url}}"}
                                                })
                                                .done(function( data ) {
                                                    $('.js-title-{{md5($url)}}').text(data.title_page);

                                                    if(data.status){
                                                        $('.js-row-{{md5($url)}}').css('background-color', 'green');
                                                    } else {
                                                        $('.js-row-{{md5($url)}}').css('background-color', 'red');
                                                    }
                                                });
                                            });
                                        </script>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>
            @endforeach
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

        <a href="{{route('email.test_campaign', ['id' => $emails->first()->id])}}" class="btn btn-primary pull-right">
            Create test campaign
        </a>
    </section>
@endsection
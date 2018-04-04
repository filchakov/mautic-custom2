@extends('scaffold-interface.layouts.app')
@section('title','Index')
@section('content')

    <section class="content">
        <h1>Please choose a segment for starting tests campaign</h1>
        <a href="{!!url('email')!!}" class = 'btn btn-primary'><i class="fa fa-home"></i> Emails</a>
        <hr/>


        <form method="POST" action="{{url()->current()}}">

            <input type="hidden" name="mautic_email_id" value="{{$email->mautic_email_id}}"/>

            <div class="form-group">
                <table class="table table-striped table-bordered table-hover" style="background: #fff">
                    <thead>
                    <tr>
                        <td width="2%"></td>
                        <td width="85%">Name</td>
                        <td width="9%" >Count Contacts</td>
                        <td width="4%">Action</td>
                    </tr>
                    </thead>
                    @foreach($segments_mautic->toArray() as $id => $value)
                        <tr>
                            <td>
                                <input type="radio" name="segment" value="{{$id}}" id="segment_id_{{$id}}" required/>
                            </td>
                            <td>
                                <label for="segment_id_{{$id}}">
                                    <b>ID {{$id}}, {{$value['name']}}</b>
                                </label>
                            </td>
                            <td style="text-align: center">
                                <div data-project_id="{{$id}}">
                                    <span class="loader"></span>
                                    <b class="js_count"></b>
                                </div>

                                <script type="application/javascript">
                                    $.get('/segment/count_leads_by_alias?alias={{$value['alias']}}', function (data) {
                                        $('[data-project_id={{$id}}] .loader').hide();
                                        $('[data-project_id={{$id}}] .js_count').text(data.count);
                                    });
                                </script>
                            </td>
                            <td>
                                <a href="https://m.hiretrail.com/s/contacts?search=segment%3A{{$value['alias']}}" class="label label-success" target="_blank">
                                    Show contacts
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>

            <input type="submit" name="" value="Create" class="btn btn-success">
        </form>


    </section>

    <style type="text/css">
        .loader {
            border: 8px solid #f3f3f3; /* Light grey */
            border-top: 8px solid #3498db; /* Blue */
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: inline-block;
            margin: auto;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
@endsection
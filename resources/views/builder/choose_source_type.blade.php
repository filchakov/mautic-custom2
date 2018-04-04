@extends('scaffold-interface.layouts.app')
@section('title', 'What is a source of this template?')

@section('content')

    <section class="content">
        <h1>What is a source of this template?</h1>

        <hr/>
        <form method="POST" action="{{route('email.create')}}" enctype="multipart/form-data">

            {{csrf_field()}}

            @foreach($projects_ids as $project_id)
                <input type="hidden" name="projects[]" value="{{$project_id}}" />
            @endforeach

            <input type="hidden" name="segment_id" value="{{$segment_id}}" />

            <div class="form-row text-center">
                    <div class="col-lg-6">
                        <label for="builder">
                            <span class="btn btn-success btn-lg">Visual builder</span>
                        </label>
                        <br/>
                        <input type="radio" name="source_type" value="builder" id="builder" required="required" checked>
                    </div>
                    <div class="col-lg-6">
                        <label for="custom_html">
                            <span class="btn btn-success btn-lg">Custom HTML</span>
                        </label>
                        <br/>
                        <input type="radio" name="source_type" value="html" id="custom_html" required="required">
                    </div>
            </div>
            <div class="form-row text-center">
                <div class="col-lg-6"></div>
                <div class="col-lg-6 text-center js_file_button" style="display: none;">
                    <div class="col-lg-3"></div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="subject_name">Subject</label>
                            <input type="text" name="subject_name" class="form-control" placeholder="Subject for email" disabled="disabled" required="required"/>
                        </div>

                        <label class="btn btn-default btn-file">
                            Add HTML file <input type="file" name="source_template" accept="text/html" disabled="disabled" required="required">
                        </label>
                    </div>

                    <div class="col-lg-3"></div>
                </div>
            </div>

            <input type="submit" class="btn btn-success pull-right" />

        </form>

    </section>

    <script type="text/javascript">
        $('[name="source_type"]').change(function () {
            if($(this).val() == 'html'){
                $('.js_file_button').show();
                $('.js_file_button input').removeAttr('disabled');
            } else {
                $('.js_file_button').hide();
                $('.js_file_button input').attr('disabled', 'disabled');
            }
        });
    </script>

@endsection
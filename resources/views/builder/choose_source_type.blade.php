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
                    <div class="col-lg-4">
                        <label for="builder">
                            <span class="btn btn-success btn-lg">Visual builder</span>
                        </label>
                        <br/>
                        <input type="radio" name="source_type" value="builder" id="builder" required="required">
                    </div>
                    <div class="col-lg-4">
                        <label for="custom_html">
                            <span class="btn btn-success btn-lg">Custom HTML</span>
                        </label>
                        <br/>
                        <input type="radio" name="source_type" value="html" id="custom_html" required="required">
                    </div>
                    <div class="col-lg-4">
                        <label for="existing_template">
                            <span class="btn btn-success btn-lg">Existing email template</span>
                        </label>
                        <br/>
                        <input type="radio" name="source_type" value="existing_template" id="existing_template" required="required">
                    </div>
            </div>
            <div class="form-row text-center">
                <div class="col-lg-4 js_wrappers"></div>
                <div class="col-lg-4 text-center js_wrappers">
                    <div class="col-lg-12 js_inputs js_html_inputs" style="display:none;">
                        <div class="form-group">
                            <label for="subject_name">Subject</label>
                            <input type="text" name="subject_name" class="form-control" placeholder="Subject for email" disabled="disabled" required="required"/>
                        </div>
                        <label class="btn btn-default btn-file">
                            Add HTML file <input type="file" name="source_template" accept="text/html" disabled="disabled" required="required">
                        </label>
                    </div>
                </div>
                <div class="col-lg-4 js_wrappers js_existing_template_inputs">
                    <div class="col-lg-12 js_inputs js_existing_template_inputs" style="display: none">
                        <div class="form-group">
                            <label for="subject_name">Subject</label>
                            <input type="text" name="subject_name" class="form-control" placeholder="Subject for email" disabled="disabled" required="required"/>
                        </div>
                        <div class="form-group">
                            <select name="based_email" id="based_email" class="form-control" required="required" disabled>
                                <option value="" disabled selected hidden>Please Choose Email...</option>
                                @foreach($emails_tree->toArray() as $email)
                                    <optgroup label="{{$email['parent']['title']}}">
                                        <option value="{{$email['parent']['id']}}" data-subject="{{$email['parent']['title']}}">{{$email['parent']['title']}} | MAIN TEMPLATE</option>

                                        @foreach($email['childs'] as $child)
                                            <option value="{{$child['id']}}" onchange="alert('dsf')" data-subject="{{$child['title']}}">{{$child['title']}} | {{$child['project']['url']}}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="col-lg-12">
                                <a href="" class="btn btn-warning js_email_preview" target="_blank">Show template</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row text-center">
                <div class="col-lg-12">
                    <input type="submit" class="btn btn-success" />
                </div>
            </div>

            <br/>
            <hr/>
        </form>

    </section>

    <script type="text/javascript">
        $('[name="source_type"]').change(function () {
            $('.js_inputs').hide();
            $('.js_inputs input, .js_inputs select').attr('disabled', 'disabled');
            $('.js_' + $(this).val() + '_inputs.js_inputs').show();
            $('.js_' + $(this).val() + '_inputs.js_inputs input').removeAttr('disabled');
            $('.js_' + $(this).val() + '_inputs.js_inputs select').removeAttr('disabled');
        });
        
        $('[name="based_email"]').change(function () {
            var title = $(this).find(':selected').data('subject');
            $('.js_existing_template_inputs [name="subject_name"]').val(title);
            $('.js_email_preview').attr('href', '/email/' + $(this).val());
        });
    </script>

@endsection
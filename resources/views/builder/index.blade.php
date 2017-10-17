<!DOCTYPE html>
<html ng-app="email">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    @if(!empty($email))
    <title>"{{$email->title}}" for {{$project->url}}</title>
    @else
    <title>New email</title>
    @endif
    <meta name="description" content="@builderDescription@">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/3.0.3/normalize.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="/email_builder/app.min.css">
</head>
<body>

<!--[if lt IE 7]>
<p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade
    your browser</a> to improve your experience.</p>
<![endif]-->

<div ng-view></div>

<script>

    window.main_template_email = {{$main_template_email}};

    window.project_id = {{isset($project->id)? $project->id : 0}};

    @if(!empty($email))
        window.email_info = {!! $email !!};
    @else
        window.email_info = {id: 0, title: ""};
    @endif

    @if(is_null($email))
            window.redirect_url = '/email';
        @elseif($email->parent_email_id != 0)
            window.redirect_url = '/email/' + '{{$email->parent_email_id}}' + '/customize';
        @else
            window.redirect_url = '/email/' + '{{$email->id}}' + '/customize';
    @endif

</script>

<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js" type="text/javascript"></script>
<script src="/email_builder/common.min.js"></script>
<!-- inject:js -->
<script src="/email_builder/config.js?time={{time()}}"></script>
<script src="/email_builder/app.js"></script>
<script src="/email_builder/builder/builder.js?time={{time()}}"></script>

<!-- endinject -->
</body>
</html>
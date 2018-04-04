@extends('scaffold-interface.layouts.app')
@section('title', $segment->name)
@section('content')

    <section class="content">

        <a href='{{route("segment.index")}}' class='btn btn-success'><i class="fa fa-reorder"></i> Back</a>

        <br/>
        <br/>

        <div class="panel panel-default">
            <div class="panel-heading">Edit: {{$segment->name}}</div>

            <div class="panel-body">

                <form method="POST" action="{{route("segment.update_main", ['id' => $segment->id])}}">
                    {{csrf_field()}}

                    <div class="form-group">
                        <label for="owner">Owner</label>
                        <div class="radio">
                            <label>
                                <input type="radio" name="owner" value="true" checked="checked">
                                For each project, this field will be selected automatically.
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="">Tags</label>
                        <div class="col-md-12">
                    @foreach($tags as $tag_id => $tag_name)
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" @if(in_array($tag_id, array_keys($segment->tags))) checked="checked" @endif  name="tags[{{$tag_id}}]" id="email_{{$tag_id}}">
                                <label for="email_{{$tag_id}}"> {{$tag_name}}</label>
                            </label>
                        </div>
                    @endforeach

                        </div>
                    </div>

                    <button class = 'btn btn-success pull-right' type ='submit'><i class="fa fa-floppy-o"></i> Update</button>

                </form>

            </div>
        </div>

    </section>
@endsection
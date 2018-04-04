<?php

namespace App\Http\Controllers;

use App\Project;
use App\Segment;
use App\SegmentsToProjects;

use Mautic\MauticApi;
use Mautic\Auth\ApiAuth;
use Illuminate\Http\Request;

class SegmentController extends Controller
{
    public function index(){

        $title = 'All segments';
        $segments = Segment::paginate(20);

        return view('segment.index',compact('title', 'segments'));
    }

    public function create(){
        $title = 'Create - segments';

        $projects = Project::all();

        $mautic = app('mautic');
        $tags = $mautic->getTagsList();

        return view('segment.create', compact('title', 'projects', 'tags'));
    }

    public function store(Request $request){

        $this->validate($request, [
            'name' => 'required|unique:segments|max:255',
            'tags' => 'required'
        ]);

        $new_segment = new Segment();
        $new_segment->name = $request->get('name');
        $new_segment->filters = [
            'tags' => array_keys($request->get('tags', []))
        ];
        $new_segment->save();

        return redirect()->route('segment.index');
    }

    public function projects($id) {

        $segment = Segment::findOrFail($id);

        $all_projects = Project::all();

        $segment_to_projects = $segment->projects()->get();

        return view('segment.projects', compact('segment', 'segment_to_projects', 'all_projects'));
    }

    public function create_for_project($id, $project_id){

        $segment = Segment::findOrFail($id);
        $project = Project::findOrFail($project_id);

        if(SegmentsToProjects::where(['segment_id' => $segment->id, 'project_id' => $project->id])->count() == 0){
            $segment_to_projects = new SegmentsToProjects();
            $segment_to_projects->project_id = $project->id;
            $segment_to_projects->segment_id = $segment->id;
            $segment_to_projects->filters = $segment->filters;
            $segment_to_projects->save();
        }

        return redirect()->route('segment.projects', ['id' => $segment->id]);
    }

    public function setting($id){
        $segment = Segment::findOrFail($id);

        $mautic = app('mautic');
        $tags = $mautic->getTagsList();

        return view('segment.setting.edit', compact('segment', 'tags'));
    }

    public function count_leads(Request $request){
        $segment =  SegmentsToProjects::where(['segment_id' => $request->get('segment_id', 0), 'project_id' => $request->get('project_id', 0)])->first();

        $count = empty($segment)? 0 : $segment->contacts_on_mautic;

        return response()->json([
            'count' => (int)$count
        ]);
    }

    public function update($id, Request $request){

        $tags = array_keys($request->get('tags', []));

        $segment = Segment::find($id);

        $segment->filters = [
            'tags' => $tags,
            'time' => time()
        ];

        $segment->save();

        return redirect(route('segment.index'));

    }

    public function count_leads_by_alias(){
        $request = \request();

        if($request->get('alias',false)){
            $mautic = app('mautic');
            $emails = $mautic->getClient('contacts')->getList('segment:' . $request->get('alias', ''), 0, 0, '', 'ASC', true, true);
        } else {
            $emails['total'] = 0;
        }

        return response()->json(['count' => (int)$emails['total']]);
    }
}

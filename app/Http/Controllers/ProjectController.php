<?php

namespace App\Http\Controllers;

use App\Email;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Project;
use Amranidev\Ajaxis\Ajaxis;
use URL;

/**
 * Class ProjectController.
 *
 * @author  The scaffold-interface created at 2017-08-30 01:23:01pm
 * @link  https://github.com/amranidev/scaffold-interface
 */
class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return  \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'All projects';
        $projects = Project::paginate(20);
        return view('project.index',compact('projects','title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return  \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'Create - project';
        
        return view('project.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param    \Illuminate\Http\Request  $request
     * @return  \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $project = new Project();

        $project->url = $request->url;

        $project->from_name = $request->from_name;
        $project->last_name = $request->last_name;
        $project->from_email = $request->from_email;
        $project->company_name = $request->company_name;
        $project->relpy_to = $request->relpy_to;
        $project->mautic_segment_id = 0;

        $url = parse_url($request->url);

        if(!empty($request->logo)){
            $filename = 'logo_' . $url['host'] . '.'.$request->file('logo')->extension();
            $request->logo->storeAs('public/logo', $filename);
            $project->logo = 'storage/logo/' . $filename;
        }

        $project->mautic_id = 0;
        $project->save();

        $pusher = App::make('pusher');

        //default pusher notification.
        //by default channel=test-channel,event=test-event
        //Here is a pusher notification example when you create a new resource in storage.
        //you can modify anything you want or use it wherever.
        $pusher->trigger('test-channel',
                         'test-event',
                        ['message' => 'A new project has been created !!']);

        if($this->checkProjects()){
            return redirect()->route('project.matcher_to_emails', ['id' => $project->id]);
        } else {
            return redirect('project');
        }
    }

    public function matcher_to_emails($id){
        $project = Project::find($id);

        $emails = Email::where([
            'project_id' => 1
        ])->get();

        return view('project.matcher_to_emails', compact('id', 'project', 'emails'));
    }

    public function copy_emails(Request $request)
    {

        $projects = $request->get('projects');
        $emails = $request->get('email');

        if(empty($projects) || empty($emails)){
            abort(400);
        }

        foreach ($projects as $project_id){
            $project = Project::find($project_id);

            foreach ($emails as $email_id => $value) {
                $email = Email::find($email_id);
                if(Email::where([
                    'parent_email_id' => $email->id,
                    'project_id' => $project->id
                ])->count() == 0){
                    $copy_email = $email->replicate(['id']);

                    $body = json_decode($copy_email->json_elements, 1)['html'];
                    $body = str_replace(['src="/', "src='/"], ['src="https://email-builder.hiretrail.com/'.$project->logo, "src='https://email-builder.hiretrail.com/".$project->logo], $body);
                    $body = str_replace(['http://dev.webscribble.com', 'https://dev.webscribble.com'], [$project->url, $project->url], $body);
                    $copy_email->body = str_replace('width:100%;height:auto;" width="100"', 'height:auto;"', $body);

                    $copy_email->parent_email_id = $email->id;
                    $copy_email->project_id = $project->id;
                    $copy_email->push();
                }
            }

        }

        return redirect()->route('email.customize', ['id' => $email_id]);

    }

    /**
     * Display the specified resource.
     *
     * @param    \Illuminate\Http\Request  $request
     * @param    int  $id
     * @return  \Illuminate\Http\Response
     */
    public function show($id,Request $request)
    {
        $title = 'Show - project';

        if($request->ajax())
        {
            return URL::to('project/'.$id);
        }

        $project = Project::findOrfail($id);
        return view('project.show',compact('title','project'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param    \Illuminate\Http\Request  $request
     * @param    int  $id
     * @return  \Illuminate\Http\Response
     */
    public function edit($id,Request $request)
    {
        $title = 'Edit - project';
        if($request->ajax())
        {
            return URL::to('project/'. $id . '/edit');
        }

        $emails = Email::where(['project_id' => 1])->whereNotIn('id', Email::where(['project_id' => $id])->pluck('parent_email_id'))->get();
        $project = Project::findOrfail($id);

        return view('project.edit',compact('title','project', 'project', 'emails', 'id'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param    \Illuminate\Http\Request  $request
     * @param    int  $id
     * @return  \Illuminate\Http\Response
     */
    public function update($id,Request $request)
    {
        $project = Project::findOrfail($id);

        $project->url = $request->url;

        $project->from_name = $request->from_name;
        $project->last_name = $request->last_name;
        $project->from_email = $request->from_email;
        $project->company_name = $request->company_name;
        $project->relpy_to = $request->relpy_to;
        $project->mautic_segment_id = $request->mautic_segment_id;

        $url = parse_url($request->url);

        if(!empty($request->logo)){
            $filename = 'logo_' . $url['host'] . '.'.$request->file('logo')->extension();
            $request->logo->storeAs('public/logo', $filename);
            $project->logo = 'storage/logo/' . $filename;
        }

        $project->save();

        return redirect('project');
    }

    /**
     * Delete confirmation message by Ajaxis.
     *
     * @link      https://github.com/amranidev/ajaxis
     * @param    \Illuminate\Http\Request  $request
     * @return  String
     */
    public function DeleteMsg($id,Request $request)
    {
        $msg = Ajaxis::BtDeleting('Warning!!','Would you like to remove This?','/project/'. $id . '/delete');

        if($request->ajax())
        {
            return $msg;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param    int $id
     * @return  \Illuminate\Http\Response
     */
    public function destroy($id)
    {
     	$project = Project::findOrfail($id);
     	$project->delete();
        return URL::to('project');
    }

    protected function checkProjects(){
        return Email::where(['project_id' => 1])->count();
    }
}

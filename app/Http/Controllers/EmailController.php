<?php

namespace App\Http\Controllers;

use App\Project;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Email;
use Amranidev\Ajaxis\Ajaxis;
use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;
use URL;

/**
 * Class EmailController.
 *
 * @author  The scaffold-interface created at 2017-08-30 01:24:28pm
 * @link  https://github.com/amranidev/scaffold-interface
 */
class EmailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return  \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'Emails';
        $emails = Email::where(['parent_email_id' => 0])->orderBy('id', 'desc')->paginate(20);
        return view('email.index',compact('emails','title'));
    }

    public function customize($id){
        $title = 'Customize email for projects';
        $projects = Project::all();

        $main_template_email = 0;

        foreach ($projects as $key => $project){
            $projects[$key]->emails = Email::where('project_id', $project->id)->first();

            if(!is_null($project->emails) && $project->parent_email_id == 0){
                $main_template_email = $project->emails->id;
            }
        }

        return view('email.customize',compact('title', 'projects', 'main_template_email'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return  \Illuminate\Http\Response
     */
    public function create()
    {

        $project = null;
        $email = null;

        $main_template_email = 'false';
        return view('builder.index', compact('project', 'email', 'main_template_email'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param    \Illuminate\Http\Request  $request
     * @return  \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        /**
         * This script will import leads to mauntic.
         */

        //($request->get('main_template_email') == 'false')? 0 : $request->get('main_template_email')

        if($request->get('main_template_email') == 'false'){
            $new_mautic_email = [
                'name' => $request->get('name'),
                'subject' => $request->get('name'),
                'customHtml' => $request->get('html'),
            ];

            $settings = ['userName'   => env('MAUTIC_LOGIN'), 'password'   => env('MAUTIC_PASSWORD'), 'debug' => true];

            $initAuth = new ApiAuth();

            $auth = $initAuth->newAuth($settings, 'BasicAuth');

            $api = new MauticApi();

            $contactApi = $api->newApi('emails', $auth, env('MAUTIC_URL'));

            $mautic_email = $contactApi->create($new_mautic_email);
        } else {
            $mautic_email['email']['id'] = Email::find($request->get('main_template_email'))->mautic_email_id;
        }

        $email = new Email();
        $email->title = $request->get('name');
        $email->body = str_replace('>null<','><',$request->get('html'));
        $email->mautic_email_id = $mautic_email['email']['id'];
        $email->project_id = ($request->get('project_id') == 0)? 1 : $request->get('project_id');
        $email->json_elements = str_replace('>null<', '><', json_encode($request->toArray()));
        $email->parent_email_id = ($request->get('main_template_email') == 'false')? 0 : $request->get('main_template_email');
        $email->save();

        $pusher = App::make('pusher');

        //default pusher notification.
        //by default channel=test-channel,event=test-event
        //Here is a pusher notification example when you create a new resource in storage.
        //you can modify anything you want or use it wherever.
        $pusher->trigger('test-channel',
                         'test-event',
                        ['message' => 'A new email has been created !!']);

        return response()->json(['status' => true]);
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
        $title = 'Show - email';

        if($request->ajax())
        {
            return URL::to('email/'.$id);
        }

        $email = Email::findOrfail($id);
        return view('email.show',compact('title','email'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param    \Illuminate\Http\Request  $request
     * @param    int  $id
     * @return  \Illuminate\Http\Response
     */
    public function edit($id,Request $request)
    {
        $title = 'Edit - email';
        if($request->ajax())
        {
            return URL::to('email/'. $id . '/edit');
        }

        
        $email = Email::findOrfail($id);
        return view('email.edit',compact('title','email'  ));
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

        $email = Email::findOrfail($id);
        $email->title = $request->get('name');
        $email->body = str_replace('>null<','><',$request->get('html'));
        $email->json_elements = str_replace('>null<', '><', json_encode($request->toArray()));
        $email->save();

        if($email->parent_email_id == 0){
            $new_mautic_email = [
                'name' => $request->get('name'),
                'subject' => $request->get('name'),
                'customHtml' => $request->get('html'),
            ];

            $settings = ['userName'   => env('MAUTIC_LOGIN'), 'password'   => env('MAUTIC_PASSWORD'), 'debug' => true];

            $initAuth = new ApiAuth();

            $auth = $initAuth->newAuth($settings, 'BasicAuth');

            $api = new MauticApi();

            $contactApi = $api->newApi('emails', $auth, env('MAUTIC_URL'));

            $contactApi->edit($email->mautic_email_id, $new_mautic_email);
        }

        return redirect('email');
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
        $msg = Ajaxis::BtDeleting('Warning!!','Would you like to remove This?','/email/'. $id . '/delete');

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
     	$email = Email::findOrfail($id);
     	$email->delete();
        return URL::to('email');
    }

    public function builder($email_id, $project_id, Request $request){

        $project = Project::findOrFail($project_id);

        $main_template_email = $request->get('main_template_email', 'false');

        if($email_id == -1){
            $email = Email::where(['id' => $main_template_email])->first();
        } else {
            $email = Email::find($email_id);
        }

        return view('builder.index', compact('project', 'email', 'main_template_email'));
    }

    public function save_image(Request $request){

        $result = [];

        if(!empty($request->upload)){
            $filename = md5(time() . $request->file('upload')->getFilename()) . '.' . $request->file('upload')->extension();
            $request->upload->storeAs('public/email/assets', $filename);

            $result['data'] = [
                'img_url' => env('APP_URL') . '/storage/email/assets/' . $filename,
                'thumb_url' => env('APP_URL') . '/storage/email/assets/' . $filename,
                'img_width' => getimagesize(storage_path().'/app/public/email/assets/'.$filename)[0]
            ];
            $result['status_code'] = 200;
            $result['status_txt'] = 'OK';
        }

        return response()->json($result);
    }

    public function api_get_markup($id, Request $request){

        $project = Project::where('url', 'like', '%'.$request->get('project').'%')->first();
        $email = Email::where(['mautic_email_id' => $id, 'project_id' => $project->id])->first();

        $body = str_replace('/email_builder/assets/default-logo.png', '/' . $project->logo, $email->body);
        $body = str_replace(['src="/', "src='/"], ['src="https://email-builder.hiretrail.com/', "src='https://email-builder.hiretrail.com/"] . $project->logo, $body);
        $body = str_replace(['http://dev.webscribble.com', 'https://dev.webscribble.com'], [$project->url, $project->url], $body);
        $body = str_replace('width:100%;height:auto;" width="100"', 'height:auto;"', $body);

        return response()->json(['body' => $body]);
    }
}

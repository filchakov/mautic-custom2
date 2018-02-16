<?php

namespace App\Http\Controllers;

use App\Jobs\CreateEmailMautic;
use App\Project;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Email;
use Amranidev\Ajaxis\Ajaxis;
use Log;
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

        $parent_email = Email::findOrFail($id);

        foreach ($projects as $key => $project){

            if(Email::where('project_id', $project->id)->where('id', $id)->count() == 1){
                $projects[$key]->emails = Email::where('project_id', $project->id)->where('id', $id)->first();
            } else {
                $projects[$key]->emails = Email::where('project_id', $project->id)->where('parent_email_id', $id)->first();
            }

            if(empty($projects[$key]->emails)){
                unset($projects[$key]);
            }

            if(!is_null($project->emails) && $project->parent_email_id == 0){
                $main_template_email = $project->emails->id;
            }
        }

        $projects_without_email = Project::whereNotIn('id', Email::where(['parent_email_id' => $id])
            ->orWhere('id', $id)
            ->pluck('project_id')
        )->get();

        return view('email.customize',compact('title', 'projects', 'main_template_email', 'id', 'projects_without_email'));
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

        if($request->get('main_template_email') != 'false'){
            $mautic_email['email']['id'] = Email::find($request->get('main_template_email'))->mautic_email_id;
        } else {
            $mautic_email['email']['id'] = 0;
        }

        $parent_email_id = ($request->get('main_template_email') == 'false')? 0 : $request->get('main_template_email');

        $json = json_decode(urldecode($request->get('email')), 1);

        $json = collect($json);

        $email = new Email();
        $email->title = $json->get('name');
        $email->body = $json->get('html');
        $email->json_elements = urldecode($request->get('email'));

        $email->utm_source = $json->get('utm_source');
        $email->utm_medium = $json->get('utm_medium');
        $email->utm_name = $json->get('utm_name');
        $email->utm_content = $json->get('utm_content');

        //$email->save();
        //$email->title = $request->get('name');
        //$email->body = str_replace('>null<','><',$request->get('html'));
        $email->mautic_email_id = $mautic_email['email']['id'];
        $email->project_id = ($request->get('project_id') == 0)? 1 : $request->get('project_id');
        //$email->json_elements = str_replace('>null<', '><', json_encode($request->toArray()));
        $email->parent_email_id = $parent_email_id;
        $email->save();

        if($parent_email_id == 0){

            $parent_email_id = $email->id;

            foreach (Project::where('id', '>', 1)->get() as $project){
                $email = new Email();
                //$email->title = $request->get('name');
                //$email->body = str_replace('>null<','><',$request->get('html'));
                $email->title = $json->get('name');
                $email->body = $json->get('html');
                $email->mautic_email_id = $mautic_email['email']['id'];
                $email->project_id = $project->id;

                $email->utm_source = $json->get('utm_source');
                $email->utm_medium = $json->get('utm_medium');
                $email->utm_name = $json->get('utm_name');
                $email->utm_content = $json->get('utm_content');

                //$email->json_elements = str_replace('>null<', '><', json_encode($request->toArray()));
                $email->json_elements = urldecode($request->get('email'));
                $email->parent_email_id = $parent_email_id;
                $email->save();
            }
        }

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
        $json = json_decode(urldecode($request->get('email')), 1);
        $json = collect($json);

        $parent_email = Email::findOrfail($id);
        $children_emails = Email::where(['parent_email_id' => $id, 'json_elements' => $parent_email->json_elements])->get();

        $emails = Email::whereIn('id', array_merge([$parent_email->id], $children_emails->pluck('id')->toArray()))->get();
        foreach ($emails as $email){
            //$email = Email::findOrfail($id);
            $email->title = $json->get('name');
            $email->body = $json->get('html');
            $email->json_elements = urldecode($request->get('email'));

            $email->utm_source = $json->get('utm_source');
            $email->utm_medium = $json->get('utm_medium');
            $email->utm_name = $json->get('utm_name');
            $email->utm_content = $json->get('utm_content');

            $email->save();

            $project = Project::find($email->project_id);

            $email->body = str_replace('/email_builder/assets/default-logo.png', '/' . $project->logo, $email->body);
            $email->body = str_replace(['src="/', "src='/"], ['src="https://email-builder.hiretrail.com/'.$project->logo, "src='https://email-builder.hiretrail.com/".$project->logo], $email->body);
            $email->body = str_replace(['http://dev.webscribble.com', 'https://dev.webscribble.com'], [$project->url, $project->url], $email->body);
            $email->body = str_replace('width:100%;height:auto;" width="100"', 'height:auto;"', $email->body);

            $email->body = str_replace([
                '{sender=project_url}',
                '{sender=company_name}',
                '{sender=first_name}',
                '{sender=last_name}',
                '{sender=email_from}',
                '{sender=email_for_reply}',
            ], [
                $project->url,
                $project->company_name,
                $project->from_name,
                $project->last_name,
                $project->from_email,
                $project->relpy_to,
            ], $email->body);

            try {
                $new_mautic_email = [
                    'name' => $email->title . ' | ' . $project->url,
                    'subject' => $email->title,
                    'customHtml' => $email->body,
                    'utmTags' => [
                        'utmSource' => $email->utm_source,
                        'utmMedium' => $email->utm_medium,
                        'utmCampaign' => $email->utm_name,
                        'utmContent' => $email->utm_content,
                    ]
                ];

                $settings = ['userName'   => env('MAUTIC_LOGIN'), 'password'   => env('MAUTIC_PASSWORD'), 'debug' => true];

                $initAuth = new ApiAuth();

                $auth = $initAuth->newAuth($settings, 'BasicAuth');

                $api = new MauticApi();

                $contactApi = $api->newApi('emails', $auth, env('MAUTIC_URL'));

                $contactApi->edit($email->mautic_email_id, $new_mautic_email);
            } catch (\Exception $e){
                Log::warninr('Change tamplate on mautic', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'host' => env('MAUTIC_URL')
                ]);
            }
        }

        return response()->json(['status' => true]);
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
        $email = Email::where(['id' => $id])->firstOrFail();
     	$email->delete();

     	$child_emails = Email::where([
     	    'parent_email_id' => $id
        ]);

     	if($child_emails->count() > 0){
     	    foreach ($child_emails->get() as $child_email){
     	        $child_email->delete();
            }
        }

        return redirect()->to('/email');
    }

    public function builder($email_id, $project_id, Request $request){

        $project = Project::findOrFail($project_id);

        $main_template_email = $request->get('main_template_email', 'false');

        if($email_id == -1){
            $email = Email::where(['id' => $main_template_email])->first();
        } else {
            $email = Email::find($email_id);
        }

        $email->json_elements = json_decode($email->json_elements,1);

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

        if(empty($email)){
            $email = Email::where(['mautic_email_id' => $id, 'project_id' => 1])->first();
        }

        $body = str_replace('/email_builder/assets/default-logo.png', '/' . $project->logo, $email->body);
        $body = str_replace(['src="/', "src='/"], ['src="https://email-builder.hiretrail.com/'.$project->logo, "src='https://email-builder.hiretrail.com/".$project->logo], $body);
        $body = str_replace(['http://dev.webscribble.com', 'https://dev.webscribble.com'], [$project->url, $project->url], $body);
        $body = str_replace('width:100%;height:auto;" width="100"', 'height:auto;"', $body);

        return response()->json(['body' => $body]);
    }
}

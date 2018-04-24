<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateEmailMautic;
use DOMDocument;
use DOMXPath;
use Log;
use SimpleXMLElement;
use URL;
use Carbon\Carbon;

use App\Email;
use App\Segment;
use App\Project;
use App\BouncedEmailLog;
use App\SegmentsToProjects;

use Illuminate\Http\Request;
use Amranidev\Ajaxis\Ajaxis;
use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;

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

        return view('email.customize', compact('title', 'projects', 'main_template_email', 'id', 'projects_without_email', 'parent_email'));
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

        $projects_ids = \request()->get('projects', []);
        $segment_id = \request()->get('segment_id', false);

        if(empty($projects_ids)){
            $projects = Project::all();
            return view('builder.choose_project', compact('projects'));
        }

        if(empty($segment_id)){
            $segments_to_projects = SegmentsToProjects::whereIn('project_id', $projects_ids)->get();
            $segments = Segment::whereIn('id', $segments_to_projects->pluck('segment_id', 'segment_id'))->get();
            return view('builder.choose_segment', compact('segments'));
        }


        if(!empty($projects_ids) && !empty($segment_id) && \request()->get('source_type', false) == false){
            $emails_tree = Email::getEmailsInTree();
            return view('builder.choose_source_type', compact('segment_id', 'projects_ids', 'emails_tree'));
        }

        if(\request()->get('source_type', false) == 'existing_template'){
            $url = $this->createEmailsFromExists(\request());
            return redirect($url);
        }

        if(\request()->get('source_type', false) == 'html'){
            $content = file_get_contents(\request()->file('source_template')->getRealPath());

            //$projects_ids
            $email = new Email();
            $email->title = \request()->get('subject_name', '');
            $email->body = $content;
            $email->json_elements = json_encode([]);

            $email->utm_source = '';
            $email->utm_medium = '';
            $email->utm_name = '';
            $email->utm_content = '';

            $email->mautic_email_id = 0;
            $email->project_id = 1;
            $email->parent_email_id = 0;
            $email->segment_id = $segment_id;
            $email->save();

            $parent_email_id = $email->id;

            foreach ($projects_ids as $projects_id){

                if($projects_id == 1){
                    continue;
                }

                $email = new Email();
                $email->title = \request()->get('subject_name', '');
                $email->body = $content;
                $email->json_elements = json_encode([]);

                $email->utm_source = '';
                $email->utm_medium = '';
                $email->utm_name = '';
                $email->utm_content = '';

                $email->mautic_email_id = 0;
                $email->project_id = $projects_id;
                $email->parent_email_id = $parent_email_id;
                $email->segment_id = $segment_id;
                $email->save();
            }

            return redirect()->route('email.customize', ['id' => $parent_email_id]);
        }

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

        $projects_ids = explode(',', $request->get('projects', ''));
        $segment_id = $request->get('segment_id', 0);

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

        $email->mautic_email_id = $mautic_email['email']['id'];
        $email->project_id = ($request->get('project_id') == 0)? 1 : $request->get('project_id');
        $email->segment_id = $segment_id;
        $email->parent_email_id = $parent_email_id;
        $email->save();

        if($parent_email_id == 0){

            $parent_email_id = $email->id;

            $projects = Project::where('id', '>', 1);

            if(!empty($projects_ids)){
                $projects->whereIn('id', $projects_ids);
            }

            foreach ($projects->get() as $project){
                $email = new Email();
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
                $email->segment_id = $segment_id;
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
        $parent_email = Email::findOrfail($id);

        if($request->get('source_type', false)){

            $children_emails = Email::where(['parent_email_id' => $id, 'body' => $parent_email->body])->get();

            $emails = Email::whereIn('id', array_merge([$parent_email->id], $children_emails->pluck('id')->toArray()))->get();

            $content = file_get_contents(\request()->file('source_template')->getRealPath());

            foreach ($emails as $email){
                $email->title = $request->get('name', '');
                $email->body = $content;

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

                $email->save();

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

                    $settings = ['userName'   => env('MAUTIC_LOGIN'), 'password'   => env('MAUTIC_PASSWORD')];

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
        } else {

            $children_emails = Email::where(['parent_email_id' => $id, 'json_elements' => $parent_email->json_elements])->get();
            $emails = Email::whereIn('id', array_merge([$parent_email->id], $children_emails->pluck('id')->toArray()))->get();

            $json = json_decode(urldecode($request->get('email')), 1);
            $json = collect($json);


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

                UpdateEmailMautic::dispatch($email)->onQueue(env('APP_ENV').'-UpdateEmailMautic');

            }
        }

        //return redirect()->route('email.customize', ['id' => $request->get('main_template_email_id', $id)]);
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

        $parent_email_id = $email->parent_email_id;
        $email->delete();

        if($parent_email_id == 0){
            $child_emails = Email::where([
                'parent_email_id' => $id
            ]);

            if($child_emails->count() > 0){
                foreach ($child_emails->get() as $child_email){
                    $child_email->delete();
                }
            }

            return redirect()->to('/email');
        } else {
            return redirect()->to(route('email.customize', ['id' => $parent_email_id]));
        }


    }

    public function builder($email_id, $project_id, Request $request){

        $project = Project::findOrFail($project_id);

        $main_template_email = $request->get('main_template_email', 'false');

        if($email_id == -1){
            $email = Email::where(['id' => $main_template_email])->first();
        } else {
            $email = Email::find($email_id);
        }

        if(empty(json_decode($email->json_elements, 1))){
            return view('email.edit_html', compact('project', 'email'));
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

    public function get_stats_email($email_id){

        try {
            $mautic = app('mautic');

            $email = Email::find($email_id);
            $emails = $mautic->getClient('emails')->get($email->mautic_email_id);

            $email_mautic['read'] = $emails['email']['readCount'];
            $email_mautic['sent'] = $emails['email']['sentCount'];
            $email_mautic['subject'] = $emails['email']['subject'];

        } catch (\Exception $e){
            $email_mautic = collect([
                'read' => 'N/A',
                'sent' => 'N/A',
                'subject' => ''
            ]);
        }

        return response()->json($email_mautic);
    }

    public function create_test_campaign(Request $request, $id){

        $email = Email::findOrFail($id);

        $campaigns = [
            'name' => 'Test campaign (' . $email->title . ')',
            'description' => 'Created via API',
            'isPublished' => 0,
            'events' => [
                [
                    'id' => 'new_44',
                    'name' => 'Send email',
                    'description' => 'API test',
                    'type' => 'email.send',
                    'eventType' => 'action',
                    'order' => 2,
                    'properties' => [
                        'email' => $email->mautic_email_id,
                        'email_type' => 'transactional',
                    ],
                    'triggerDate' => null,
                    'triggerMode' => 'immediate',
                    'children' => [],
                    'decisionPath' => 'yes',
                ]
            ],
            'forms' => [],
            'lists' => [
                [
                    'id' => (int)$request->get('segment', 0) // Create the list first
                ]
            ],
            'canvasSettings' => [
                'nodes' => [
                    [
                        'id' => 'new_44', // Event ID will be replaced on /new
                        'positionX' => '433',
                        'positionY' => '348',
                    ],
                    [
                        'id' => 'lists',
                        'positionX' => '629',
                        'positionY' => '65',
                    ],
                ],
                'connections' => [
                    [
                        'sourceId' => 'lists',
                        'targetId' => 'new_44', // Event ID will be replaced on /new
                        'anchors' => [
                            'source' => 'leadsource',
                            'target' => 'top',
                        ]
                    ]
                ]
            ]
        ];

        $mautic = app('mautic');
        $campaign = $mautic->getClient('campaigns');
        $result = $campaign->create($campaigns);


        return redirect()->to(env('MAUTIC_URL') . '/s/campaigns/view/' . $result['campaign']['id']);
    }

    public function test_campaign($id){

        $email = Email::findOrFail($id);

        $mautic = app('mautic');
        $emails = $mautic->getClient('segments')->getList();

        $campaigns_from_mautic = collect($emails['lists']);

        $segments_mautic = $campaigns_from_mautic
            ->mapWithKeys(function ($item) use ($mautic){

                return [$item['id'] => [
                    'name' => $item['name'],
                    'alias' => $item['alias']
                ]];
            })
            ->except(SegmentsToProjects::all()->pluck('mautic_segment_id')->merge([29])->toArray());

        return view('email.create_test_campaign', compact('segments_mautic', 'email'));
    }

    public function bounced($id){
        $bounced = BouncedEmailLog::where('project_id', $id)->get();
        $project = Project::findOrFail($id);

        $filename = 'Bounced-emails-' . $project->company_name . ' ' . Carbon::now()->format('Y-m-d') . '.csv';

        return response(implode("\r\n", $bounced->pluck('email')->toArray()), 200, [
            'Content-type' => 'text/csv',
            'Content-disposition' => 'filename="' . $filename . '"'
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    private function createEmailsFromExists($request)
    {
        $projects_ids = $request->get('projects', []);
        $segment_id = $request->get('segment_id', false);

        $source_email = Email::where('id', (int)$request->get('based_email'))->first();

        $parent_email = $source_email->replicate();

        try {
            $json_element = json_decode($parent_email->json_elements, 1);
            $json_element['name'] = $request->get('subject_name');
            $parent_email->json_elements = json_encode($json_element);
        } catch (\Exception $e){

        }

        $parent_email->project_id = 1;
        $parent_email->title = $request->get('subject_name');
        $parent_email->parent_email_id = 0;
        $parent_email->mautic_email_id = 0;
        $parent_email->segment_id = $segment_id;
        $parent_email->save();

        foreach ($projects_ids as $projects_id){
            $child_email = $parent_email->replicate();
            $child_email->parent_email_id = $parent_email->id;
            $child_email->project_id = $projects_id;
            $child_email->save();
        }

        return route('email.builder', ['email_id' => $parent_email->id, 'project_id' => 1]);
    }

    public function tests(Request $request){

        $json = json_decode(urldecode($request->get('body')), 1);
        $json = collect($json);

        $project_ids = $request->get('projects', []);

        $projects = Project::whereIn('id', $project_ids)->get();

        \App\Providers\ElasticEmailClient\ApiClient::SetApiKey('1d12d50e-fd8a-46cc-8d71-ad073f79eb83');

        foreach ($projects as $project){

            $subject = $json->get('name');
            $bodyHtml = $json->get('html');


            $bodyHtml = str_replace('/email_builder/assets/default-logo.png', '/' . $project->logo, $bodyHtml);
            $bodyHtml = str_replace(['src="/', "src='/"], ['src="https://email-builder.hiretrail.com/'.$project->logo, "src='https://email-builder.hiretrail.com/".$project->logo], $bodyHtml);
            $bodyHtml = str_replace(['http://dev.webscribble.com', 'https://dev.webscribble.com'], [$project->url, $project->url], $bodyHtml);
            $bodyHtml = str_replace('width:100%;height:auto;" width="100"', 'height:auto;"', $bodyHtml);

            $bodyHtml = str_replace([
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
            ], $bodyHtml);


            $utm_tags = [
                'utm_source' => $json->get('utm_source'),
                'utm_medium' => $json->get('utm_medium'),
                'utm_name' => $json->get('utm_name'),
                'utm_content' => $json->get('utm_content'),
            ];

            if(implode('', array_values($utm_tags)) != ''){
                $bodyHtml = preg_replace_callback('#(<a.*?href=")([^"]*)("[^>]*?>)#i', function($match) use ($utm_tags) {
                    $url = $match[2];
                    if (strpos($url, '?') === false) {
                        $url .= '?';
                    } else {
                        $url .= '&';
                    }
                    $url .= 'utm_source=' . $utm_tags['utm_source'] . '&utm_medium=' . $utm_tags['utm_medium'] . '&utm_name=' . urlencode($utm_tags['utm_name']) . '&utm_content=' . $utm_tags['utm_content'];
                    return $match[1] . $url . $match[3];
                }, $bodyHtml);
            }



            $dom = new DomDocument();
            @$dom->loadHTML($bodyHtml);

            $finder = new DomXPath($dom);

            $cl_job_item = 'rss-job-item';
            $cl_title = "rss-title";
            $cl_description = "rss-description";

            $item_job = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $cl_job_item ')]");
            $count_jobs = $item_job->length;

            if(!empty($count_jobs)){
                $xml_feed_url = $item_job->item(0)->getAttribute('data-rss-url');
                $xml_feed_data = $this->getFeedData($xml_feed_url);

                for ($i = 0; $i < $count_jobs; $i++){

                    $item_from_rss_feed = current($xml_feed_data);

                    $rss_title = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $cl_title ')]")->item($i);
                    $rss_title->textContent = $item_from_rss_feed->title;
                    $rss_title->setAttribute('href', $item_from_rss_feed->link);

                    $rss_description = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $cl_description ')]")->item($i);
                    $rss_description->textContent = strip_tags($item_from_rss_feed->description);

                    next($xml_feed_data);
                }
            }

            $bodyHtml = $finder->document->saveHTML();


            $to = ['filchakov.denis@gmail.com', 'gretchen@webscribble.com'];

            $EEemail = new \App\Providers\ElasticEmailClient\Email();

            try
            {
                $response = $EEemail->Send(
                    $subject,
                    $project->from_email,
                    $project->company_name,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    $to,
                    array(),
                    array(),
                    array(),
                    array(),
                    array(),
                    null,
                    null,
                    null,
                    $bodyHtml,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null
                );
            }
            catch (\Exception $e)
            {
                \Log::warning('Test email: attempt was failed', ['message' => $e->getMessage()]);
            }
        }

        return response(['status' => true]);
    }

    /**
     * @param $xml_feed_url
     * @return array
     */
    private function getFeedData($xml_feed_url)
    {
        $xml_feed_url = str_replace(' ','', $xml_feed_url);

        $content = file_get_contents($xml_feed_url);

        if(strpos($content, '</channel>') === false){
            $content .= '</channel></rss>';
        }

        try {
            $feed = new SimpleXMLElement($content);
            $result = ((array)$feed->channel)['item'];
        } catch (\Exception $e){
            $result = [];
        }

        return $result;
    }
}

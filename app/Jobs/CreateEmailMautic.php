<?php

namespace App\Jobs;

use App\Campaign;
use Log;

use Mautic\MauticApi;
use Mautic\Auth\ApiAuth;

use App\Email;
use App\Project;
use App\SegmentsToProjects;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateEmailMautic implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Email
     */
    protected $model = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email)
    {
        $this->model = $email;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $model = $this->model;

            $project = Project::find($model->project_id);

            $model->body = str_replace('/email_builder/assets/default-logo.png', '/' . $project->logo, $model->body);
            $model->body = str_replace(['src="/', "src='/"], ['src="https://email-builder.hiretrail.com/'.$project->logo, "src='https://email-builder.hiretrail.com/".$project->logo], $model->body);
            $model->body = str_replace(['http://dev.webscribble.com', 'https://dev.webscribble.com'], [$project->url, $project->url], $model->body);
            $model->body = str_replace('width:100%;height:auto;" width="100"', 'height:auto;"', $model->body);

            $model->body = str_replace([
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
            ], $model->body);


            $new_mautic_email = [
                'name' => $model->title . ' | ' . $project->url,
                'subject' => $model->title,
                'customHtml' => $model->body,
                'utmTags' => [
                    'utmSource' => $model->utm_source,
                    'utmMedium' => $model->utm_medium,
                    'utmCampaign' => $model->utm_name,
                    'utmContent' => $model->utm_content,
                ]
            ];

            $settings = ['userName'   => env('MAUTIC_LOGIN'), 'password'   => env('MAUTIC_PASSWORD'), 'debug' => true];

            $initAuth = new ApiAuth();

            $auth = $initAuth->newAuth($settings, 'BasicAuth');

            $api = new MauticApi();

            $emailsApi = $api->newApi('emails', $auth, env('MAUTIC_URL'));

            $mautic_email = $emailsApi->create($new_mautic_email);

            $model->mautic_email_id = $mautic_email['email']['id'];
            $model->save();

            //creating campaign
            $campaignsApi = $api->newApi('campaigns', $auth, env('MAUTIC_URL'));

            $segment_to_project = SegmentsToProjects::where([
                'project_id' => $project->id,
                'segment_id' => $model->segment_id
            ])->first();

            if(empty($segment_to_project)){
                Log::warning('Don\'t exists segment for this project');
                return false;
            }

            $campaigns = [
                'name' => $model->title . ' | ' . $project->url,
                'description' => 'Created via API',
                'isPublished' => 0,
                'events' => [
                    [
                        'id' => 'new_44', // Event ID will be replaced on /new
                        'name' => 'Send email',
                        'description' => 'API test',
                        'type' => 'email.send',
                        'eventType' => 'action',
                        'order' => 2,
                        'properties' => [
                            'email' => $mautic_email['email']['id'], // Create email first
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
                        'id' => $segment_to_project->mautic_segment_id // Create the list first
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

            $campaigns = $campaignsApi->create($campaigns);

            $campaign = new Campaign();
            $campaign->email_id = $this->model->id;
            $campaign->campaign_id_mautic = $campaigns['campaign']['id'];
            $campaign->segment_id = $model->segment_id;
            $campaign->project_id = $model->project_id;
            $campaign->save();



            Log::info('Campaign ', [
                $this->model->id,
                $campaigns['campaign']['id'],
                $model->segment_id,
                $model->project_id
            ]);

            Log::info('$campaigns ', $campaigns);
        } catch (\Exception $e){
            Log::warning('Problem with creating email', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

    }
}

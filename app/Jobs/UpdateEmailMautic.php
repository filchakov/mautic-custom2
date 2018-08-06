<?php

namespace App\Jobs;

use App\Campaign;
use App\Project;

use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateEmailMautic implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model = null;

    /**
     * Create a new job instance.
     *
     * @param $email
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
        $email = $this->model;

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

            $settings = ['userName'   => env('MAUTIC_LOGIN'), 'password'   => env('MAUTIC_PASSWORD')];

            $initAuth = new ApiAuth();

            $auth = $initAuth->newAuth($settings, 'BasicAuth');

            $api = new MauticApi();

            $contactApi = $api->newApi('emails', $auth, env('MAUTIC_URL'));

            $contactApi->edit($email->mautic_email_id, $new_mautic_email);


            $campaign = Campaign::where([
                'email_id' => $this->model->id,
                'segment_id' => $this->model->segment_id,
                'project_id' => $this->model->project_id
            ])->firstOrFail();


            $campaignApi = $api->newApi('campaigns', $auth, env('MAUTIC_URL'));
            $campaignApi->edit($campaign->campaign_id_mautic, [
                'name' => $email->title . ' | ' . $project->url,
            ]);

        } catch (\Exception $e){
            Log::warninr('Change tamplate on mautic', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'host' => env('MAUTIC_URL')
            ]);
        }
    }
}

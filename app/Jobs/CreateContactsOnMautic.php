<?php

namespace App\Jobs;

use App\Project;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;

class CreateContactsOnMautic implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $request = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        try {
            $request = $this->request;
            $request['project_url'] = 'http://dev.webscribble.com/';

            $url = parse_url($request['project_url']);
            $project = Project::where('url', 'like', '%' . $url['host'] . '%')->first();

            if (!empty($request['tags'])) {
                $tags = explode(',', $request['tags']);
            } else {
                $tags = [];
            }

            $settings = ['userName' => env('MAUTIC_LOGIN'), 'password' => env('MAUTIC_PASSWORD'), 'debug' => true];

            $initAuth = new ApiAuth();

            $auth = $initAuth->newAuth($settings, 'BasicAuth');

            $api = new MauticApi();

            $contactApi = $api->newApi('contacts', $auth, env('MAUTIC_URL'));

            $contact = $contactApi->create(
                array_merge([
                    'owner' => $project->mautic_id,
                    'tags' => $tags
                ], $request)
            );

            if (!empty($contact['contact'])) {

                $result_data = [
                    'id' => $contact['contact']['id'],
                    'tags' => implode(',', $tags),
                ];

                foreach ($request as $name => $value) {
                    if (isset($contact['contact']['fields']['core'][$name])) {
                        $result_data[$name] = $contact['contact']['fields']['core'][$name]['value'];
                    }
                }

                \Log::debug('CreateContactsOnMautic: success created a contact', $result_data);

                return true;
            }

        } catch (\Exception $e) {
            var_dump([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            \Log::warning('CreateContactsOnMautic: failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw $e;
        }

    }
}

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

        $request = $this->request;

        $errors = [];

        $code = 400;

        $project_url = $request['project_url'];

        if(empty($project_url)){
            $errors['project_url'] = 'Missing field';
        } else {
            $project = Project::where('url', 'like', '%'.$project_url.'%')->first();
            if(empty($project)) {
                $errors['project_url'] = 'URL does not exist in a database';
                $code = 404;
            }
        }


        if(!empty($request['tags'])){
            $tags = explode(',', $request['tags']);
        } else {
            $tags = [];
        }

        $email = $request['email'];

        if(empty($email)) {
            $errors['project_url'] = 'Missing field';
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $errors['email'] = 'Is not valid email';
        }

        if(!empty($errors)){
            return \response()->json([
                'status' => false,
                'data' => $errors
            ], $code);

        } else {
            $settings = ['userName'   => env('MAUTIC_LOGIN'), 'password'   => env('MAUTIC_PASSWORD'), 'debug' => true];

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

            if(!empty($contact['contact'])){

                $result_data = [
                    'id' => $contact['contact']['id'],
                    'tags' => implode(',', $tags),
                ];

                foreach ($request as $name => $value) {
                    if(isset($contact['contact']['fields']['core'][$name])){
                        $result_data[$name] = $contact['contact']['fields']['core'][$name]['value'];
                    }
                }

                return response()->json([
                    'status' => true,
                    'data' => $result_data
                ], 200);
            }
        }
    }
}

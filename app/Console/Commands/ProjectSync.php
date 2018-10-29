<?php

namespace App\Console\Commands;

use App\Project;
use Illuminate\Console\Command;
use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;

class ProjectSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:force_create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $projects = Project::where('mautic_id', 0)->get();

        foreach ($projects as $project) {
            try {
                $url = parse_url($project->url);

                $project->url = $url['scheme'] . '://' . $url['host'];

                $new_mautic_email = [
                    'username' => $project->url,
                    'firstName' => $project->from_name,
                    'lastName' => $project->last_name,
                    'email' => $project->from_email,
                    'plainPassword' => array(
                        'password' => md5($project->url.$project->url),
                        'confirm' => md5($project->url.$project->url),
                    ),
                    'role' => 1,
                ];

                $settings = ['userName' => env('MAUTIC_LOGIN'), 'password' => env('MAUTIC_PASSWORD'), 'debug' => true];

                $initAuth = new ApiAuth();

                $auth = $initAuth->newAuth($settings, 'BasicAuth');
                $api = new MauticApi();
                $contactApi = $api->newApi('users', $auth, env('MAUTIC_URL'));

                $result = $contactApi->create($new_mautic_email);

                $project->mautic_id = $result['user']['id'];

                $segmentsApi = $api->newApi('segments', $auth, env('MAUTIC_URL'));
                $segments = [
                    'name' => '(' . $project->from_name . ' ' . $project->last_name . ') ' . $project->url,
                    'alias' => time(),
                    'filters' => [
                        [
                            'glue' => 'and',
                            'field' => 'owner_id',
                            'object' => 'lead',
                            'type' => 'lookup_id',
                            'filter' => $project->mautic_id,
                            'display' => $project->from_name . ' ' . $project->last_name,
                            'operator' => '='
                        ]
                    ],
                    'isPublished' => 1
                ];

                $segmentResult = $segmentsApi->create($segments);
                $project->mautic_segment_id = $segmentResult['list']['id'];

                $project->save();
            } catch (\Exception $e){
                $this->warn($e->getMessage());
            }
        }
    }
}

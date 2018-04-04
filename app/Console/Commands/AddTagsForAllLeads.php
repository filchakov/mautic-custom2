<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;

class AddTagsForAllLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:add_tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
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
        $settings = ['userName'   => env('MAUTIC_LOGIN'), 'password'   => env('MAUTIC_PASSWORD'), 'debug' => true];
        $initAuth = new ApiAuth();
        $auth = $initAuth->newAuth($settings, 'BasicAuth');
        $api = new MauticApi();

        $emailsApi = $api->newApi('contacts', $auth, env('MAUTIC_URL'));

        $per_page = 500;
        $page = 0;

        $contacts = $emailsApi->getList("", $page, $per_page)['contacts'];

        $leads_updated = 1;

        while (!empty($contacts)){

            $page++;
            $offset = $page*$per_page;

            $this->info('Page ' . $page . ', offset ' . $offset);


            $contacts = $emailsApi->getList("", $page*$per_page, $per_page)['contacts'];

            $this->info('Get total contacts: ' . count($contacts));

            foreach($contacts as $contact){

                if(empty($contact['owner'])){

                    $this->warn('Lead #' . $contact['id'] . ' without owner');

                    $row = [
                        $contact['id'],
                        'https://m.hiretrail.com/s/contacts/edit/'.$contact['id'],
                        $contact['dateAdded'],
                        $contact['fields']['core']['firstname']['value'],
                        $contact['fields']['core']['lastname']['value'],
                        $contact['fields']['core']['company']['value'],
                        $contact['fields']['core']['position']['value'],
                        $contact['fields']['core']['email']['value'],
                    ];

                    continue;
                }

                $this->info('Lead #' . $contact['id'] . ', added tag job_seeker. Page ' . $page);

                $emailsApi->edit($contact['id'], [
                    'tags' => ['job_seeker']
                ]);

                $this->info('Total leads were updated ' . $leads_updated);

                $leads_updated++;
            }
        }
    }
}

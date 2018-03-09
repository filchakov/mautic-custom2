<?php

namespace App\Console\Commands;

use App\BouncedEmailLog;
use App\Project;
use Illuminate\Console\Command;

use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;

class DeleteBouncedContacts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contacts:bounced';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var \Mautic\Api\Api
     */
    protected $contacsApi = null;

    /**
     * Create a new command instance.
     *
     * @throws \Mautic\Exception\ContextNotFoundException
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $settings = ['userName'   => env('MAUTIC_LOGIN'), 'password'   => env('MAUTIC_PASSWORD'), 'debug' => true];

        $initAuth = new ApiAuth();
        $auth = $initAuth->newAuth($settings, 'BasicAuth');

        $api = new MauticApi();
        $this->contacsApi = $api->newApi('contacts', $auth, env('MAUTIC_URL'));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        /**
         * Read contacts from segment START
         */
        $bounced_contacts = $this->contacsApi->getList('segment:Bounced', 0, 1000);

        foreach ($bounced_contacts['contacts'] as $bounced_contact){

            $bounced_email = $bounced_contact['fields']['core']['email']['value'];

            try {
                $this->info('Delete #' . $bounced_contact['id'] . ' ' . $bounced_email);
                $this->updateProject($bounced_contact);
                $this->contacsApi->delete($bounced_contact['id']);
            } catch (\Exception $e){
                $this->error($e->getMessage() . ', ' . $bounced_email);
            }
        }
        /**
         * Read contacts from segment FINISH
         */

    }

    /**
     * @param array $contact
     * @return bool
     */
    private function updateProject($contact = []){

        $project = Project::where('mautic_id', $contact['owner']['id'])->first();

        if(!empty($project)){
            $project->bounced_emails = $project->bounced_emails + 1;
            $project->save();

            //Create a record in log
            $bounced_email_log = new BouncedEmailLog();
            $bounced_email_log->email = $contact['fields']['core']['email']['value'];
            $bounced_email_log->project_id = $project->id;
            $bounced_email_log->save();

            return true;
        }

        return false;

    }
}

<?php

namespace App\Console\Commands;

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
        $contacsApi = $api->newApi('contacts', $auth, env('MAUTIC_URL'));

        /**
         * Read contacts from segment START
         */
        $bounced_contacts = $contacsApi->getList('segment:Bounced', 0, 1000);
        foreach ($bounced_contacts['contacts'] as $bounced_contact){
            $bounced_email = $bounced_contact['fields']['core']['email']['value'];
            try {
                $this->info('Delete #' . $bounced_contact['id'] . ' ' . $bounced_email);
                $contacsApi->delete($bounced_contact['id']);
            } catch (\Exception $e){
                $this->error($e->getMessage() . ', ' . $bounced_email);
            }
        }
        /**
         * Read contacts from segment FINISH
         */

    }
}

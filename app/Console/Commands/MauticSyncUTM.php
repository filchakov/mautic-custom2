<?php

namespace App\Console\Commands;

use App\Email;
use Illuminate\Console\Command;
use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;

class MauticSyncUTM extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mautic:sync-utm';

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
        $settings = ['userName'   => env('MAUTIC_LOGIN'), 'password'   => env('MAUTIC_PASSWORD'), 'debug' => true];

        $initAuth = new ApiAuth();

        $auth = $initAuth->newAuth($settings, 'BasicAuth');

        $api = new MauticApi();

        $emails = Email::where('mautic_email_id', '>', 0)->orderByDesc('id')->get();

        foreach ($emails as $email){
            sleep(1);
            $emailsApi = $api->newApi('emails', $auth, env('MAUTIC_URL'));

            $utm_setting = collect($emailsApi->get($email->mautic_email_id)['email']['utmTags']);

            $email->utm_source = $utm_setting->get('utmSource');
            $email->utm_medium = $utm_setting->get('utmMedium');
            $email->utm_name = $utm_setting->get('utmCampaign');
            $email->utm_content = $utm_setting->get('utmContent');
            $email->save();

            $this->info('Success updated emails #' . $email->id);
        }

    }
}

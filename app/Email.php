<?php

namespace App;

use App\Jobs\CreateEmailMautic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;

/**
 * Class Email.
 *
 * @author  The scaffold-interface created at 2017-08-30 01:24:28pm
 * @link  https://github.com/amranidev/scaffold-interface
 */
class Email extends Model
{

    protected $fillable = [
        'title'
    ];

    protected $dates = ['deleted_at'];

    protected $table = 'emails';


    static function boot()
    {
        parent::boot();

        static::deleting(function($model){
            $mautic_id = $model->mautic_email_id;

            $settings = ['userName'   => env('MAUTIC_LOGIN'), 'password'   => env('MAUTIC_PASSWORD'), 'debug' => true];

            $initAuth = new ApiAuth();
            $auth = $initAuth->newAuth($settings, 'BasicAuth');
            $api = new MauticApi();

            $emailsApi = $api->newApi('emails', $auth, env('MAUTIC_URL'));


            $emailsApi->edit($mautic_id, [
                'isPublished' => 0
            ]);
        });

        static::created(function ($model) {
            CreateEmailMautic::dispatch($model)->onQueue(env('APP_ENV').'-CreateEmailMautic');
        });
    }
}

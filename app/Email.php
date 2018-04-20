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


    public function project()
    {
        return $this->hasOne('App\Project', 'id', 'project_id')->select(['id', 'logo', 'url']);
    }

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


    /**
     * @param bool $is_unique
     * @return \Illuminate\Support\Collection
     */
    public static function getEmailsInTree($is_unique = true)
    {
        $result = collect();

        $main_emails = Email::where([
            'project_id' => 1,
            'parent_email_id' => 0
        ])
            ->with('project')
            ->select(['id', 'title', 'body', 'project_id'])
            ->orderBy('id', 'desc')
            ->get();

        foreach ($main_emails as $email) {
            $tmp_result = [
                'parent' => collect($email->toArray())->forget('body'),
                'childs' => []
            ];

            $childs = Email::where('parent_email_id', '=', $email->id)
                ->where('body', '!=', $email->body)
                ->select(['id', 'title', 'project_id'])
                ->with('project')
                ->get();

            $tmp_result['childs'] = $childs->toArray();

            $result->push($tmp_result);
        }

        return $result;
    }

}

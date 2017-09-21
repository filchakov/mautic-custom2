<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;

/**
 * Class Project.
 *
 * @author  The scaffold-interface created at 2017-08-30 01:23:01pm
 * @link  https://github.com/amranidev/scaffold-interface
 */
class Project extends Model
{

    protected $dates = ['deleted_at'];

    protected $table = 'projects';

    public function emails()
    {
        return $this->hasMany('App\Email', 'project_id', 'id');
    }

    static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $url = parse_url($model->url);
            $model->url = $url['scheme'] . '://' . $url['host'];

            $new_mautic_email = [
                'username' => $model->url,
                'firstName' => $model->from_name,
                'lastName' => $model->last_name,
                'email' => $model->from_email,
                'plainPassword' => array(
                    'password' => $model->url.$model->url,
                    'confirm' => $model->url.$model->url,
                ),
                'role' => 1,
            ];

            $settings = ['userName' => env('MAUTIC_LOGIN'), 'password' => env('MAUTIC_PASSWORD'), 'debug' => true];

            $initAuth = new ApiAuth();

            $auth = $initAuth->newAuth($settings, 'BasicAuth');

            $api = new MauticApi();
            $contactApi = $api->newApi('users', $auth, env('MAUTIC_URL'));

            $result = $contactApi->create($new_mautic_email);

            $model->mautic_id = $result['user']['id'];

            $model->save();

        });

    }

}

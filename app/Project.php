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

    /**
     * @return array
     */
    public function getSegmentsCounterAttribute(){
        return [
            'total' => Segment::count(),
            'created' => SegmentsToProjects::where('project_id', $this->id)->count()
        ];
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

            $segmentsApi = $api->newApi('segments', $auth, env('MAUTIC_URL'));
            $segments = [
                'name' => '(' . $model->from_name . ' ' . $model->last_name . ') ' . $model->url,
                'alias' => time(),
                'filters' => [
                    [
                        'glue' => 'and',
                        'field' => 'owner_id',
                        'object' => 'lead',
                        'type' => 'lookup_id',
                        'filter' => $model->mautic_id,
                        'display' => $model->from_name . ' ' . $model->last_name,
                        'operator' => '='
                    ]
                ],
                'isPublished' => 1
            ];

            $segmentResult = $segmentsApi->create($segments);
            $model->mautic_segment_id = $segmentResult['list']['id'];

            $model->save();


            $segments = Segment::all();

            foreach ($segments as $segment) {

                if(SegmentsToProjects::where(['segment_id' => $segment->id, 'project_id' => $model->id])->count() == 0){
                    $segment_to_projects = new SegmentsToProjects();
                    $segment_to_projects->project_id = $model->id;
                    $segment_to_projects->segment_id = $segment->id;
                    $segment_to_projects->filters = $segment->filters;
                    $segment_to_projects->save();
                }

                sleep(rand(1,3));
            }

        });

    }

}

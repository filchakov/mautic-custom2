<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;

class SegmentsToProjects extends Model
{
    protected $fillable = [
        'filters'
    ];
    protected $casts = [
        'filters' => 'array'
    ];

    public function project(){
        return $this->hasOne(Project::class, 'id', 'project_id');
    }

    public function segment(){
        return $this->hasOne(Segment::class, 'id', 'segment_id');
    }

    public function getContactsOnMauticAttribute(){

        if(empty($this->mautic_segment_id)){
            return 0;
        } else {
            $segment = $this->getMauticClient('segments')->get($this->mautic_segment_id);
            $contacts = $this->getMauticClient('contacts')->getList('segment:' . $segment['list']['alias'], 0, 0, '', 'ASC', true, true);
            return $contacts['total'];
        }
    }

    public function getSegmentAliasAttribute(){
        $segment = $this->getMauticClient('segments')->get($this->mautic_segment_id);
        return $segment['list']['alias'];
    }


    /**
     * @param string $name
     * @return \Mautic\Api\Api
     */
    private function getMauticClient($name = ''){
        $settings = ['userName'   => env('MAUTIC_LOGIN'), 'password'   => env('MAUTIC_PASSWORD'), 'debug' => true];

        $initAuth = new ApiAuth();
        $auth = $initAuth->newAuth($settings, 'BasicAuth');

        $api = new MauticApi();
        return $api->newApi($name, $auth, env('MAUTIC_URL'));
    }

    public static function boot()
    {
        parent::boot();

        $initAuth = new ApiAuth();
        $auth = $initAuth->newAuth(['userName'   => env('MAUTIC_LOGIN'), 'password'   => env('MAUTIC_PASSWORD'), 'debug' => true], 'BasicAuth');

        $api = new MauticApi();
        $mautic_client = $api->newApi('segments', $auth, env('MAUTIC_URL'));

        static::creating(function ($model) use ($mautic_client){

            $name_owner = trim($model->project()->first()->from_name) . ' ' . trim($model->project()->first()->last_name);

            $segments = [
                'name' => $model->segment()->first()->name . ' (' . $name_owner . ' ' . $model->project()->first()->url . ') ' . time(),
                'alias' => time(),
                'filters' => [
                    [
                        'glue' => 'and',
                        'field' => 'owner_id',
                        'object' => 'lead',
                        'type' => 'lookup_id',
                        'filter' => $model->project()->first()->mautic_id,
                        'display' => $name_owner,
                        'operator' => '='
                    ],
                    [
                        'glue' => 'and',
                        'field' => 'tags',
                        'object' => 'lead',
                        'type' => 'tags',
                        'filter' => $model->filters['tags'],
                        'operator' => 'in',
                        'display' => NULL
                    ]
                ],
                'isPublished' => 1
            ];

            //TODO uncomment
            $segmentResult = $mautic_client->create($segments);
            $model->mautic_segment_id = $segmentResult['list']['id'];
        });

    }
}

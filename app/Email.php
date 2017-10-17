<?php

namespace App;

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

            $project = Project::find($model->project_id);

            $model->body = str_replace('/email_builder/assets/default-logo.png', '/' . $project->logo, $model->body);
            $model->body = str_replace(['src="/', "src='/"], ['src="https://email-builder.hiretrail.com/'.$project->logo, "src='https://email-builder.hiretrail.com/".$project->logo], $model->body);
            $model->body = str_replace(['http://dev.webscribble.com', 'https://dev.webscribble.com'], [$project->url, $project->url], $model->body);
            $model->body = str_replace('width:100%;height:auto;" width="100"', 'height:auto;"', $model->body);

            $model->body = str_replace([
                '{sender=project_url}',
                '{sender=company_name}',
                '{sender=first_name}',
                '{sender=last_name}',
                '{sender=email_from}',
                '{sender=email_for_reply}',
            ], [
                $project->url,
                $project->company_name,
                $project->from_name,
                $project->last_name,
                $project->from_email,
                $project->reply_to,
            ], $model->body);


            $new_mautic_email = [
                'name' => $model->title . ' | ' . $project->url,
                'subject' => $model->title,
                'customHtml' => $model->body,
            ];

            $settings = ['userName'   => env('MAUTIC_LOGIN'), 'password'   => env('MAUTIC_PASSWORD'), 'debug' => true];

            $initAuth = new ApiAuth();

            $auth = $initAuth->newAuth($settings, 'BasicAuth');

            $api = new MauticApi();

            $emailsApi = $api->newApi('emails', $auth, env('MAUTIC_URL'));

            $mautic_email = $emailsApi->create($new_mautic_email);

            $model->mautic_email_id = $mautic_email['email']['id'];
            $model->save();

            //creating segment
            $segmentsApi = $api->newApi('segments', $auth, env('MAUTIC_URL'));
            $segments = [
                'name' => $model->title . ' | ' . $project->url,
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
            $segments = $segmentsApi->create($segments);
            \Log::info('$segments ', $segments);
            //finish creating segment

            //creating campaign
            $campaignsApi = $api->newApi('campaigns', $auth, env('MAUTIC_URL'));

            $campaigns = [
                'name' => $model->title . ' | ' . $project->url,
                'description' => 'Created via API',
                'isPublished' => 0,
                'events' => [
                    [
                        'id' => 'new_44', // Event ID will be replaced on /new
                        'name' => 'Send email',
                        'description' => 'API test',
                        'type' => 'email.send',
                        'eventType' => 'action',
                        'order' => 2,
                        'properties' => [
                            'email' => $mautic_email['email']['id'], // Create email first
                            'email_type' => 'transactional',
                        ],
                        'triggerDate' => null,
                        'triggerMode' => 'immediate',
                        'children' => [],
                        'decisionPath' => 'yes',
                    ]
                ],
                'forms' => [],
                'lists' => [
                    [
                        'id' => $segments['list']['id'] // Create the list first
                    ]
                ],
                'canvasSettings' => [
                    'nodes' => [
                        [
                            'id' => 'new_44', // Event ID will be replaced on /new
                            'positionX' => '433',
                            'positionY' => '348',
                        ],
                        [
                            'id' => 'lists',
                            'positionX' => '629',
                            'positionY' => '65',
                        ],
                    ],
                    'connections' => [
                        [
                            'sourceId' => 'lists',
                            'targetId' => 'new_44', // Event ID will be replaced on /new
                            'anchors' => [
                                'source' => 'leadsource',
                                'target' => 'top',
                            ]
                        ]
                    ]
                ]
            ];

            $campaigns = $campaignsApi->create($campaigns);
            \Log::info('$campaigns ', $campaigns);

        });
    }
}

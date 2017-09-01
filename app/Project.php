<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public function emails(){
        return $this->hasMany('App\Email', 'project_id', 'id');
    }

    static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $url = parse_url($model->url);
            $model->url = $url['scheme'] . '://' . $url['host'];
            $model->save();
        });

    }

}

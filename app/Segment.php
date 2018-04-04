<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    protected $casts = [
        'filters' => 'array'
    ];

    public function projects(){
        return $this->hasMany(SegmentsToProjects::class, 'segment_id', 'id');
    }

    /**
     * @return array
     */
    public function getTagsAttribute(){

        $mautic = app('mautic');
        $tags_list = $mautic->getTagsList();

        $result = [];
        if(!empty($this->filters) && !empty($this->filters['tags'])){
            foreach ($this->filters['tags'] as $tag_id){
                if(isset($tags_list[$tag_id])){
                    $result[$tag_id] = $tags_list[$tag_id];
                }
            }
        }

        return $result;
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $projects = Project::all();

            foreach ($projects as $project){
                $segment_to_projects = new SegmentsToProjects();
                $segment_to_projects->project_id = $project->id;
                $segment_to_projects->segment_id = $model->id;
                $segment_to_projects->filters = $model->filters;
                $segment_to_projects->save();
            }
        });

        static::updating(function ($model){
            $segment_to_project = SegmentsToProjects::where(['segment_id' => $model->id, 'project_id' => 1]);

            $original = $model->getOriginal();

            if($segment_to_project->count()){
                $segment_to_project = $segment_to_project->first();
                $segment_to_project->filters = $model->filters;
                $segment_to_project->save();
            }

            SegmentsToProjects::where(['segment_id' => $model->id])
                ->whereRaw('(filters = "[]" OR filters = ' . json_encode($original['filters']) . ')')
                ->update([
                    'filters' => json_encode($model->filters)
                ]);
        });

    }

}

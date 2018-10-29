<?php

namespace App\Console\Commands;

use App\Project;
use App\Segment;
use App\SegmentsToProjects;
use Illuminate\Console\Command;

class CreateSegments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'segments:create';

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
        $projects = Project::all();

        foreach ($projects as $project) {

            if(
                $project->segments_counter['total'] > $project->segments_counter['created']
            ){
                $segmentsId = SegmentsToProjects::where(['project_id' => $project->id,])->pluck('segment_id');
                $segments = Segment::whereNotIn('id', $segmentsId)->get();

                foreach ($segments as $segment) {
                    $this->info('Segment - ' . $segment->id . ', project - ' . $project->id);

                    if(SegmentsToProjects::where(['segment_id' => $segment->id, 'project_id' => $project->id])->count() == 0){
                        $segment_to_projects = new SegmentsToProjects();
                        $segment_to_projects->project_id = $project->id;
                        $segment_to_projects->segment_id = $segment->id;
                        $segment_to_projects->filters = $segment->filters;
                        $segment_to_projects->save();
                    }

                    sleep(rand(1,3));
                }
            }
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Project;
use App\Segment;
use App\SegmentsToProjects;
use Illuminate\Console\Command;

class MatchOldMauticSegments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mautic:match_old_segments';

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
        $mautic = app('mautic');

        $segments = $mautic->getClient('segments')->getList('');

        /**
         * tag_id => segment_id
         */
        $tags = [
            9 => 1,
            8 => 2
        ];


        $result = [];

        foreach ($tags as $tag => $segment_id){
            foreach ($segments['lists'] as $segment){

                $owner_id = 0;

                foreach ($segment['filters'] as $filter){
                    if($filter['field'] == 'owner_id'){
                        $owner_id = $filter['filter'];
                    }
                }

                foreach ($segment['filters'] as $filter){
                    if($filter['field'] == 'tags' && in_array($tag, $filter['filter'])){
                        $result[$tag][] = [
                            'segment_id' => $segment['id'],
                            'segment_name' => $segment['name'],
                            'owner_id' => $owner_id
                        ];
                    }
                }
            }
        }


        foreach ($result as $tag_id => $values){
            $segment = Segment::where('filters', json_encode(['tags' => [$tag_id]]))->first();

            foreach ($values as $value){

                if(in_array($value['segment_id'], [14, 27, 29, 30])){
                    $this->warn('CONTINUE: ' . json_encode($value));
                    continue;
                }

                $this->warn(json_encode($value));
                $segments_to_projects = new SegmentsToProjects();
                $segments_to_projects->segment_id = $segment->id;
                $segments_to_projects->project_id = Project::where('mautic_id', $value['owner_id'])->first()->id;
                $segments_to_projects->filters = $segment->filters;
                $segments_to_projects->mautic_segment_id = $value['segment_id'];
                $segments_to_projects->save();
                $this->info('Tag: ' . $tag_id . ', Segments_to_projects ' . $segments_to_projects->id);
            }
        }

        $this->info('DONE');
    }
}

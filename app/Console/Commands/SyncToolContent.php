<?php

namespace App\Console\Commands;

use App\Tools\ToolContentSynchronizer;
use App\Tools\ToolRegistry;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tools:sync-content')]
#[Description('Create database records for every tool and add missing default content keys, keeping edited values')]
class SyncToolContent extends Command
{
    public function handle(ToolRegistry $tools, ToolContentSynchronizer $synchronizer): int
    {
        $rows = [];

        foreach ($tools->keys() as $toolKey) {
            $record = $synchronizer->recordFor($toolKey);
            $created = $record->wasRecentlyCreated;
            $added = $created ? $record->contentFields->count() : $synchronizer->addMissingFields($record);

            $rows[] = [$toolKey, $created ? 'created' : 'existing', $added, $record->contentFields->count()];

            foreach ($record->children()->with(['contentFields', 'parent'])->get() as $child) {
                $rows[] = ["  └ {$child->key}", 'language version', $synchronizer->addMissingFields($child), $child->contentFields->count()];
            }
        }

        $this->table(['Tool', 'Record', 'Keys added', 'Total keys'], $rows);
        $this->components->info('Tool content is in the database and can be edited in the admin.');

        return self::SUCCESS;
    }
}

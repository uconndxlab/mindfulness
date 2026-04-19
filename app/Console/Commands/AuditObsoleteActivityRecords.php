<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Content;
use App\Models\Journal;
use App\Models\Quiz;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class AuditObsoleteActivityRecords extends Command
{
    protected $signature = 'activities:audit-obsolete-records';

    protected $description = 'Audit content, quizzes, and journals for activity type mismatches';

    public function handle(): int
    {
        $activityTypesById = Activity::query()->pluck('type', 'id');

        $contentActivityCount = Activity::query()
            ->whereIn('type', ['practice', 'lesson'])
            ->count();
        $reflectionActivityCount = Activity::query()->where('type', 'reflection')->count();
        $journalActivityCount = Activity::query()->where('type', 'journal')->count();

        $contentCount = Content::query()->count();
        $quizCount = Quiz::query()->count();
        $journalCount = Journal::query()->count();

        $obsoleteContentIds = $this->findObsoleteIds(
            Content::query()->select('id', 'activity_id')->get(),
            $activityTypesById,
            ['practice', 'lesson']
        );
        $obsoleteQuizIds = $this->findObsoleteIds(
            Quiz::query()->select('id', 'activity_id')->get(),
            $activityTypesById,
            ['reflection']
        );
        $obsoleteJournalIds = $this->findObsoleteIds(
            Journal::query()->select('id', 'activity_id')->get(),
            $activityTypesById,
            ['journal']
        );

        $ghostActivities = $this->findGhostActivities($activityTypesById);

        $this->info('Activity type audit');
        $this->table(
            ['Entity', 'Activity count', 'DB record count', 'Status'],
            [
                ['Content (practice + lesson)', $contentActivityCount, $contentCount, $this->countStatus($contentActivityCount, $contentCount)],
                ['Quiz (reflection)', $reflectionActivityCount, $quizCount, $this->countStatus($reflectionActivityCount, $quizCount)],
                ['Journal (journal)', $journalActivityCount, $journalCount, $this->countStatus($journalActivityCount, $journalCount)],
            ]
        );

        $this->newLine();
        $this->line('Obsolete content IDs: ' . $this->formatIds($obsoleteContentIds));
        $this->line('Obsolete quiz IDs: ' . $this->formatIds($obsoleteQuizIds));
        $this->line('Obsolete journal IDs: ' . $this->formatIds($obsoleteJournalIds));

        $this->newLine();
        $this->info('Ghost Activities (in DB but not in database/data/activities.json):');

        if ($ghostActivities === null) {
            $this->error('  Could not load database/data/activities.json. Skipping ghost activity check.');
        } elseif ($ghostActivities->isEmpty()) {
            $this->line('  None. All DB activities are accounted for in the seed file.');
        } else {
            $this->warn('  Found ' . $ghostActivities->count() . ' activity record(s) not present in the seed file:');
            $this->table(
                ['ID', 'Type', 'Title', 'Day ID'],
                $ghostActivities
                    ->map(fn ($activity) => [
                        $activity->id,
                        $activity->type,
                        $activity->title,
                        $activity->day_id,
                    ])
                    ->all()
            );
            $this->line('  Ghost Activity IDs: ' . $ghostActivities->pluck('id')->implode(', '));
        }

        if (
            $obsoleteContentIds->isNotEmpty() ||
            $obsoleteQuizIds->isNotEmpty() ||
            $obsoleteJournalIds->isNotEmpty()
        ) {
            $this->warn('Obsolete records found. Review IDs above before deleting.');
        } else {
            $this->info('No obsolete records found.');
        }

        return self::SUCCESS;
    }

    private function findGhostActivities(Collection $activityTypesById): ?Collection
    {
        $path = database_path('data/activities.json');

        if (!is_file($path)) {
            return null;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return null;
        }

        $decoded = json_decode($contents, true);

        if (!is_array($decoded)) {
            return null;
        }

        $seedIds = collect($decoded)
            ->pluck('id')
            ->filter(fn ($id) => $id !== null)
            ->map(fn ($id) => (int) $id)
            ->all();

        $seedIdLookup = array_flip($seedIds);

        $missingIds = $activityTypesById
            ->keys()
            ->reject(fn ($id) => array_key_exists((int) $id, $seedIdLookup))
            ->values();

        if ($missingIds->isEmpty()) {
            return collect();
        }

        return Activity::query()
            ->whereIn('id', $missingIds)
            ->orderBy('id')
            ->get(['id', 'day_id', 'title', 'type']);
    }

    private function findObsoleteIds(Collection $records, Collection $activityTypesById, array $expectedTypes): Collection
    {
        return $records
            ->filter(function ($record) use ($activityTypesById, $expectedTypes) {
                $activityType = $activityTypesById->get($record->activity_id);

                if ($activityType === null) {
                    return true;
                }

                return !in_array($activityType, $expectedTypes, true);
            })
            ->pluck('id')
            ->sort()
            ->values();
    }

    private function countStatus(int $activityCount, int $recordCount): string
    {
        if ($activityCount === $recordCount) {
            return 'OK';
        }

        $difference = $recordCount - $activityCount;
        $delta = $difference > 0 ? "+{$difference}" : (string) $difference;

        return "WARNING ({$delta})";
    }

    private function formatIds(Collection $ids): string
    {
        if ($ids->isEmpty()) {
            return 'none';
        }

        return $ids->implode(', ');
    }
}

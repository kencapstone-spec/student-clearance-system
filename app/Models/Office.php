<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'group',
        'sort_order',
        'is_final_approver',
    ];

    protected $casts = [
        'is_final_approver' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function clearanceApprovals(): HasMany
    {
        return $this->hasMany(ClearanceApproval::class);
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class);
    }

    public function prerequisites(): BelongsToMany
    {
        return $this->belongsToMany(Office::class, 'office_prerequisites', 'office_id', 'prerequisite_office_id');
    }

    /**
     * Sort a collection of offices topologically according to their prerequisites.
     *
     * Offices with 0 prerequisites come first.
     * Offices that depend on others come after their prerequisites.
     * Final approvers (e.g., President) appear at the very end.
     * Ties are broken by sort_order, then name.
     */
    public static function sortByPrerequisites($offices)
    {
        $collection = $offices instanceof Collection ? $offices : collect($offices);
        if ($collection->isEmpty()) {
            return $collection;
        }

        $officeMap = $collection->keyBy('id');
        $depths = [];
        $visiting = [];

        $getDepth = function ($officeId) use (&$getDepth, &$depths, &$visiting, $officeMap) {
            if (isset($depths[$officeId])) {
                return $depths[$officeId];
            }

            if (isset($visiting[$officeId])) {
                return 0; // Guard against cyclic prerequisites
            }

            $office = $officeMap->get($officeId);
            if (! $office) {
                return 0;
            }

            if ($office->is_final_approver) {
                $depths[$officeId] = 99999;

                return 99999;
            }

            $prereqs = $office->relationLoaded('prerequisites')
                ? $office->prerequisites
                : $office->prerequisites()->get(['id']);

            if ($prereqs->isEmpty()) {
                $depths[$officeId] = 0;

                return 0;
            }

            $visiting[$officeId] = true;
            $maxPrereqDepth = 0;
            foreach ($prereqs as $prereq) {
                $prereqDepth = $getDepth($prereq->id);
                if ($prereqDepth > $maxPrereqDepth) {
                    $maxPrereqDepth = $prereqDepth;
                }
            }
            unset($visiting[$officeId]);

            $depth = $maxPrereqDepth + 1;
            $depths[$officeId] = $depth;

            return $depth;
        };

        foreach ($collection as $office) {
            $getDepth($office->id);
        }

        return $collection->sort(function ($a, $b) use ($depths) {
            $depthA = $depths[$a->id] ?? 0;
            $depthB = $depths[$b->id] ?? 0;

            if ($depthA !== $depthB) {
                return $depthA <=> $depthB;
            }

            if ($a->sort_order !== $b->sort_order) {
                return $a->sort_order <=> $b->sort_order;
            }

            return strcmp($a->name, $b->name);
        })->values();
    }
}

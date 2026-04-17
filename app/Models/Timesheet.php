<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;



class Timesheet extends Model implements AuditableContract
{
    use SoftDeletes, AuditableTrait, HasFactory;
   


    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'user_id',
        'start_time',
        'end_time',
        'clock_in_mode',
        'type',
        'notes',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];
    protected $appends = [
    'duration_hours',
];
    /**
     * Only audit important fields
     */
    protected $auditInclude = [
        'start_time',
        'end_time',
        'type',
        'notes',
    ];

    /**
     * Optional: ignore noisy timestamps
     */
    protected $auditExclude = [
        'updated_at',
    ];


    //  | RELATIONSHIPS

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //  | SCOPES

    /**
     * Only open shifts (not clocked out)
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('end_time');
    }

    /**
     * Closed shifts
     */
    public function scopeClosed(Builder $query): Builder
    {
        return $query->whereNotNull('end_time');
    }

    /**
     * Filter by user visibility
     * - Admin/Superadmin see all
     * - Users see only their own
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasRole(['Admin User', 'Super Admin'])) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }

    /**
     * Filter by date range
     */
    public function scopeBetween(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('start_time', [
            Carbon::parse($from)->startOfDay(),
            Carbon::parse($to)->endOfDay(),
        ]);
    }

    //  | ACCESSORS

    /**
     * Duration in seconds
     */
//  public function getDurationSecondsAttribute(): int
// {
//     if (!$this->end_time || $this->end_time->lessThan($this->start_time)) {
//         return 0;
//     }

//     return $this->end_time->diffInSeconds($this->start_time);
// }

public function getDurationSecondsAttribute(): int
{
    if (!$this->start_time || !$this->end_time) {
        return 0;
    }

    // GUARANTEED POSITIVE
    return max(
        0,
        $this->start_time->diffInSeconds($this->end_time)
    );
}
    /**
     * Duration in hours (decimal)
     */
    // public function getDurationHoursAttribute(): float
    // {
    //     return round($this->duration_seconds / 3600, 2);
    // }
public function getDurationHoursAttribute(): float
{
    return round($this->duration_seconds / 3600, 2);
}
    /**
     * Formatted time range (for list/week view)
     * Example: 7:55am - 12:07pm
     */
    public function getTimeRangeAttribute(): string
    {
        if (!$this->end_time) {
            return $this->start_time->format('g:ia') . ' - running';
        }

        return $this->start_time->format('g:ia') . ' - ' . $this->end_time->format('g:ia');
    }

    //  | HELPERS

    /**
     * Check if user has an open shift
     */
    public static function hasOpenShift(int $userId): bool
    {
        if (!Schema::hasTable((new self())->getTable())) {
            return false;
        }

        return static::where('user_id', $userId)
            ->whereNull('end_time')
            ->exists();
    }

    /**
     * Get open shift for user
     */
    public static function openShiftFor(int $userId): ?self
    {
        if (!Schema::hasTable((new self())->getTable())) {
            return null;
        }

        return static::where('user_id', $userId)
            ->whereNull('end_time')
            ->first();
    }

    /**
     * Clock out this timesheet
     */
    public function clockOut(?Carbon $time = null): void
    {
        $this->update([
            'end_time' => $time ?? now(),
        ]);
    }

    /**
     * Auto close open shifts before today (midnight job)
     */
   public static function autoCloseOpenShifts(): int
    {
        if (!Schema::hasTable((new self())->getTable())) {
            return 0;
        }

        return static::whereNull('end_time')
            ->whereDate('start_time', '<', now()->toDateString())
            ->update([
                'end_time' => DB::raw("DATE_ADD(DATE(start_time), INTERVAL 23 HOUR 59 MINUTE 59 SECOND)")
            ]);
    }

    protected static function booted()
    {
        static::saving(function (Timesheet $timesheet) {

            if (
                $timesheet->start_time &&
                $timesheet->end_time &&
                $timesheet->end_time->lessThan($timesheet->start_time)
            ) {
                // Auto-fix instead of throwing exception
                $timesheet->end_time = null;
            }
        });
    }

    public static function currentShiftForUser(int $userId): ?self
    {
        if (!Schema::hasTable((new self())->getTable())) {
            return null;
        }

        return self::where('user_id', $userId)
            ->whereNull('end_time')
            ->latest('start_time')
            ->first();
    }
    // Timesheet.php

    public function getClockInModeLabelAttribute(): string
    {
        return match ($this->clock_in_mode) {
            'office'           => 'Office',
            'remote'           => 'Remote',
            'out_of_office'    => 'Out of Office',
            'do_not_disturb'   => 'Do Not Disturb',
            default            => ucfirst(str_replace('_', ' ', $this->clock_in_mode)),
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'normal_paid'        => 'Normal Paid',
            'absent_unpaid'      => 'Absent (Unpaid)',
            'compensated_paid'   => 'Compensated Paid',
            'holiday_paid'       => 'Holiday Paid',
            'sick_paid'          => 'Sick Paid',
            'vacation_paid'      => 'Vacation Paid',
            'vacation_unpaid'    => 'Vacation Unpaid',
            default              => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

public function transformAudit(array $data): array
{
    $data['meta'] = [
        'action_reason' => request()->get('reason'),
        'source'        => request()->route()?->getName(),
    ];

    return $data;
}


}

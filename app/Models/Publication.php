<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Publication extends Model implements Auditable
{
    use SoftDeletes, AuditableTrait;

    protected $table = 'publications';

    protected $fillable = [
        'publication_type_id',
        'name',
        'code',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $auditInclude = [
        'publication_type_id',
        'name',
        'code',
        'description',
        'status',
    ];

    protected $auditExclude = [
        'updated_at',
    ];

    public function transformAudit(array $data): array
    {
        $request = request();
        $data['meta'] = [
            'action_reason' => $request ? $request->get('reason') : null,
            'source'        => $request && $request->route() ? $request->route()->getName() : null,
        ];
        return $data;
    }

    public function publicationType(): BelongsTo
    {
        return $this->belongsTo(PublicationType::class, 'publication_type_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'publication_user',
            'publication_id',
            'user_id'
        )->withTimestamps();
    }
}

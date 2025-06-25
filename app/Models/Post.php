<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'hotness',
        'view_count',
    ];

    protected $casts = [
        'hotness' => 'decimal:2',
        'view_count' => 'integer',
        'user_id' => 'integer',
    ];

    /**
     * Relation with author
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation with views
     *
     * @return HasMany
     */
    public function userViews(): HasMany
    {
        return $this->hasMany(UserPostView::class);
    }

    /**
     * Order by hotness
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByHotness($query, string $direction = 'desc')
    {
        return $query->orderBy('hotness', $direction);
    }

    /**
     * Check if the post has been viewed by the user.
     *
     * @param string $userId
     * @return bool
     */
    public function isViewedByUser(string $userId): bool
    {
        return $this->userViews()
            ->where('user_id', $userId)
            ->exists();
    }
}

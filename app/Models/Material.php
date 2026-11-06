<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Material extends Model
{
    protected $fillable = [
        'category_id',
        'subject_id',
        'created_by',
        'title',
        'description',
        'content',
        'pdf_url',
        'video_url',
    ];

    protected $appends = [
        'pdf_full_url',
        'video_full_url',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * URL lengkap file PDF.
     */
    public function getPdfFullUrlAttribute(): ?string
    {
        if (!$this->pdf_url) {
            return null;
        }

        return Storage::disk('public')->url($this->pdf_url);
    }

    /**
     * URL lengkap file video.
     */
    public function getVideoFullUrlAttribute(): ?string
    {
        if (!$this->video_url) {
            return null;
        }

        return Storage::disk('public')->url($this->video_url);
    }
}

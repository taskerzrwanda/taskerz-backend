<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote',
        'author_name',
        'author_title',
        'company',
        'media_type',
        'media_path'
    ];

    protected $appends = ['media_url'];

    /**
     * Get the full media URL
     */
    public function getMediaUrlAttribute()
    {
        return $this->media_path;
    }

    /**
     * Scope to get only testimonials with images
     */
    public function scopeImages($query)
    {
        return $query->where('media_type', 'image');
    }

    /**
     * Scope to get only testimonials with videos
     */
    public function scopeVideos($query)
    {
        return $query->where('media_type', 'video');
    }
}

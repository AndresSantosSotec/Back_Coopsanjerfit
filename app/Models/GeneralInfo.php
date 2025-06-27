<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GeneralInfo extends Model
{
    protected $fillable = [
        'title', 'content', 'category', 'image_path', 'video_path',
    ];

    // Para que al serializar al JSON incluya estos atributos
    protected $appends = ['image_url', 'video_url'];

    public function getImageUrlAttribute()
    {
        return $this->image_path
            ? asset(Storage::url($this->image_path))
            : null;
    }

    public function getVideoUrlAttribute()
    {
        return $this->video_path
            ? asset(Storage::url($this->video_path))
            : null;
    }
}

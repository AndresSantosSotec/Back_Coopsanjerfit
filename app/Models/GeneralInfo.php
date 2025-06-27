<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'category',
        'image_path',
        'video_path',
    ];

    /** Estos atributos virtuales se incluirán en las respuestas JSON */
    protected $appends = [
        'image_url',
        'video_url',
    ];

    /**
     * Obtiene la URL pública de la imagen
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path
            ? \Illuminate\Support\Facades\Storage::url($this->image_path)
            : null;
    }

    /**
     * Obtiene la URL pública del video
     */
    public function getVideoUrlAttribute(): ?string
    {
        return $this->video_path
            ? \Illuminate\Support\Facades\Storage::url($this->video_path)
            : null;
    }
}

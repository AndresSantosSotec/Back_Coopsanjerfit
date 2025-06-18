<?php
// app/Models/Activity.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'exercise_type',
        'duration',
        'duration_unit',
        'intensity',
        'calories',
        'steps',
        'selfie_path',
        'device_image_path',
        'attachments',
        'notes',
        'location_lat',
        'location_lng',
    ];

    /** Estos atributos virtuales se incluirán automáticamente
     *  cuando el modelo se serialice a JSON. De esta forma las
     *  URLs de las imágenes estarán disponibles en las respuestas
     *  del API sin necesidad de transformaciones adicionales. */
    protected $appends = [
        'selfie_url',
        'device_image_url',
        'attachments_url',
    ];

    protected $casts = [
        'attachments'   => 'array',
        'location_lat'  => 'decimal:6',
        'location_lng'  => 'decimal:6',
    ];

    // Relación con usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accesores para URLs públicas
    public function getSelfieUrlAttribute()
    {
        return $this->selfie_path
            ? Storage::url($this->selfie_path)
            : null;
    }

    public function getDeviceImageUrlAttribute()
    {
        return $this->device_image_path
            ? Storage::url($this->device_image_path)
            : null;
    }

    public function getAttachmentsUrlAttribute()
    {
        return collect($this->attachments ?? [])
            ->map(fn($path) => Storage::url($path))
            ->all();
    }
}

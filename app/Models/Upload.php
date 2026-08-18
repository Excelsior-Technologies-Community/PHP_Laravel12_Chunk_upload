<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    protected $fillable = [
        'file_name',
        'original_name',
        'file_path',
        'mime_type',
        'extension',
        'file_size',
        'checksum',
        'status',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;

        if ($bytes === 0) {
            return '0 Bytes';
        }

        $units = [
            'Bytes',
            'KB',
            'MB',
            'GB',
            'TB',
        ];

        $power = floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);

        return round($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}

<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Activitylog\Traits\LogsActivity;

class Role extends SpatieRole
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'guard_name',
        'code',
        'display_name',
        'description',
        'order',
        'color',
        'status',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Danh sách tên màu Tailwind phổ biến
     */
    public static $colors = ['red', 'blue', 'green', 'yellow', 'purple', 'pink', 'indigo', 'gray', 'orange', 'teal', 'cyan', 'emerald', 'lime', 'amber', 'rose', 'violet', 'fuchsia', 'sky', 'slate', 'zinc', 'neutral', 'stone'];

    protected static $logAttributes = [
        'name',
        'guard_name',
        'code',
        'display_name',
        'description',
        'order',
        'color',
        'status',
    ];

    /**
     * Generate unique Tailwind color name for role
     */
    public static function generateUniqueColor()
    {
        $existingColors = self::pluck('color')->filter()->toArray();
        $availableColors = array_diff(self::$colors, $existingColors);
        if (empty($availableColors)) {
            // Nếu hết màu, cho phép trùng lại
            $availableColors = self::$colors;
        }
        return collect($availableColors)->random();
    }

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logOnly(static::$logAttributes)
            ->logOnlyDirty();
    }
} 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'instructor_id',
        'session_date',
        'start_time',
        'end_time',
        'qr_code_token',
        'qr_code_image',
        'gps_required',
        'nfc_active',
        'classroom_name',
        'classroom_latitude',
        'classroom_longitude',
        'status',
    ];

    protected $casts = [
        'session_date' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'gps_required' => 'boolean',
        'nfc_active' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class, 'session_id');
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function close()
    {
        $this->update(['status' => 'closed']);
    }
}

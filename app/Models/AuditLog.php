<?php
namespace App\Models;
class AuditLog extends ContentModel
{
    public function user() { return $this->belongsTo(User::class); }
}

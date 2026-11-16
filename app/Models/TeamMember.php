<?php

namespace App\Models;

class TeamMember extends ContentModel
{
    protected function casts(): array { return ['is_active'=>'boolean']; }
}

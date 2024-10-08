<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamUser extends Model
{
    use HasFactory;

    protected $table = 'team_user';

    protected $fillable = [
        'team_id', 'user_id', 'lead_id'
    ];

    public function team()
    {
        return $this->belongsTo(Teams::class);
    }
}

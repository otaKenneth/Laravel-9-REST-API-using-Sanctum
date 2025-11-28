<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role',
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function userDetail()
    {
        return $this->hasOne(UserDetails::class);
    }

    public function myTeam()
    {
        return $this->hasOne(Teams::class, 'user_id', 'id');
    }

    public function precedingTeamLead()
    {
        return $this->myTeam->leads()->first() ? $this->myTeam->leads()->first()->precedingTeamLead()->merge([$this->myTeam->leads()->first()]):collect();
    }

    public function role()
    {
        return $this->belongsTo(Roles::class, 'roles_id', 'id');
    }

    public function stores()
    {
        return $this->hasMany(Stores::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transactions::class);
    }

    public function team()
    {
        return $this->belongsToMany(Teams::class, 'team_user', 'team_id')
                ->withPivot('lead_id');
    }

    public function ledTeams()
    {
        return $this->hasMany(Teams::class);
    }

}

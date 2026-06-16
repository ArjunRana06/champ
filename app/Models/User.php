<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'xp', 'level', 'streak', 'last_active_date', 'badges'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'xp' => 'integer',
            'level' => 'integer',
            'streak' => 'integer',
            'last_active_date' => 'datetime',
            'badges' => 'array',
        ];
    }

    // app/Models/User.php

    // Inside the User class, add:
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}

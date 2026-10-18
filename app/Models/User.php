<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
   protected $fillable = [
    'name', 'email', 'password',
    'documento', 'telefono', 'direccion',
    'fecha_nacimiento', 'rol', 'activo',
    'cambiar_password',
];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'fecha_nacimiento'  => 'date',
        'activo'            => 'boolean',
        'cambiar_password'  => 'boolean',
    ];
    }

    public function docente() { return $this->hasOne(Docente::class); }
public function acudiente() { return $this->hasOne(Acudiente::class); }
}

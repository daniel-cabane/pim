<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'email', 'password', 'google_id', 'email_verified_at', 'preferences'];

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
        'password' => 'hashed',
        'preferences' => 'object'
    ];

    protected $appends = ['is'];

    public function getIsAttribute()
    {
      return [
          'student' => $this->email_verified_at != null && $this->hasRole('student'),
          'publisher' => $this->email_verified_at != null && $this->hasRole('publisher'),
          'teacher' => $this->email_verified_at != null && $this->hasRole('teacher'),
          'hod' => $this->email_verified_at != null && $this->hasRole('hod'),
          'admin' => $this->email_verified_at != null && $this->hasRole('admin')
      ];
    }

    public function posts()
    {
      return $this->hasMany(Post::class, 'author_id');
    }

    public function workshops()
    {
      return $this->hasMany(Workshop::class, 'organiser_id')->where('archived', 0);
    }

    public function archivedWorkshops()
    {
      return $this->hasMany(Workshop::class, 'organiser_id')->where('archived', 1);
    }

    public function enrollements()
    {
      return $this->belongsToMany(Workshop::class)->withPivot(['comment', 'available', 'confirmed']);
    }

    public function scopeTeachers($query)
    {
        return $query->whereHas("roles", function($q){ $q->where("name", "teacher"); });
    }

    public function getFormalNameAttribute()
    {
      $name = $this->name;
      if($this->is['teacher']){
        $nameParts = explode(' ', $this->name);
        if(count($nameParts) > 1){
          array_shift($nameParts);
        }
        $name = implode(' ', $nameParts);
        if($this->preferences->title){
          $name = $this->preferences->title." ".$name;
        }
      }
      if($this->is['student']){
        $nameParts = explode(' ', $this->name);
        $name = $nameParts[0]." ";
        array_shift($nameParts);
        foreach($nameParts as $namePart){
          $name .= substr($namePart,0,1).".";
        }
      }

      return $name;
    }

    // public function teachers()
    // {
    //     return $this->hasMany(User::class, 'user_id')->whereHas("roles", function($q){ $q->where("name", "teacher"); });
        // if($this->hasRole('hod') || $this->hasRole('admin')){
        //     return User::whereHas("roles", function($q){ $q->where("name", "teacher"); })->get();
        // }

        // return null;
    // }
}

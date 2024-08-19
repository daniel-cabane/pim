<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $fillable = ['from', 'to', 'body', 'status'];

    public function sender()
    {
      return $this->belongsTo(User::class, 'from');
    }

    public function receiver()
    {
      return $this->belongsTo(User::class, 'to');
    }

    public function format()
    {
        return [
            'id' => $this->id,
            'sender' => [
                'id' => $this->from,
                'name' => $this->sender->name,
                'email' => $this->sender->email
            ],
            'body' => $this->body,
            'status' => $this->status,
            'sentOn' => $this->created_at
        ];
    }
}

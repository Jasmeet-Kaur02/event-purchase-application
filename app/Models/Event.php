<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        "title",
        "description",
        "venue",
        "ticketPrize",
        "dateTime",
        "totalSeats"
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'userEvents', "eventId", "userId");
    }
}

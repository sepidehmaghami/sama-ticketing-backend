<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'priority',
        'status',
        'type',
        'archived'
    ];

    public function contents() {
        return $this->hasMany(TicketContent::class);
    }
}

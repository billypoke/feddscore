<?php

namespace FeddScore;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'score', 'place', 'disqualified'];

    public function competition()
    {
        return $this->belongsTo('FeddScore\Competition');
    }
}

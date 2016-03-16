<?php

namespace FeddScore;

use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
    public $timestamps = false;

    protected $fillable = ['year', 'ampm', 'name', 'status'];

    public function teams()
    {
        return $this
            ->hasMany('FeddScore\Team')
            ->orderBy('teams.place', 'desc')
            ->orderBy('teams.score', 'desc');
    }
}

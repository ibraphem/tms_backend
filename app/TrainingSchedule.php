<?php

namespace App;
use App\Trainee;
use App\Course;

use Illuminate\Database\Eloquent\Model;

class TrainingSchedule extends Model
{
    public function trainee()
    {
        return $this->belongsTo('App\Trainee');
    }

    public function course()
    {
        return $this->belongsTo('App\Course');
    }
    
}

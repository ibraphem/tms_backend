<?php

namespace App;
use App\TrainingSchedule;

use Illuminate\Database\Eloquent\Model;

class Trainee extends Model
{
    public function training_schedule()
    {
        return $this->belongsTo('App\TrainingSchedule');
    }
}

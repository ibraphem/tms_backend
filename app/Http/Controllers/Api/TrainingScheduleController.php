<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\TrainingSchedule;
use App\Course;
use App\Trainee;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DB;

class TrainingScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = DB::table('training_schedules')->Distinct()
       
        ->leftJoin('instructors', 'training_schedules.instructor_id', '=', 'instructors.id')
        ->orderBy('training_schedules.created_at', 'DESC')
        ->get(['schedule_id', 'training_title', 'full_name', 'venue', 'training_start_date', 'instructor_id', 'training_end_date',
        'cost', 'training_type', 'validity']);

        return $result;
    }

    public function fetchCompletedTrainings() {
        $result = DB::table('training_schedules')
        ->where('training_status', '=', 1)
        ->leftJoin('trainees', 'training_schedules.trainee_id', '=', 'trainees.id')
        ->orderBy('training_schedules.created_at', 'DESC')
        ->get(['training_schedules.id', 'trainees.first_name', 'trainees.surname', 'training_schedules.training_title', 
        'training_schedules.training_start_date', 'training_schedules.training_end_date', 
        'training_schedules.cost', 'training_schedules.expiry_date', 'training_schedules.sent_date']);
        return $result;
    }

    public function fetchParticipants($schedule_id) {

        $result = DB::table('training_schedules')
     
        ->leftJoin('trainees', 'training_schedules.trainee_id', '=', 'trainees.id')
        ->select('training_schedules.id', 'trainees.surname',  'trainees.staff_id', 'trainees.first_name', 
        'training_schedules.training_status')
        ->where('schedule_id', $schedule_id)
        ->get();

        return $result;

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function test($year=2020, $month=9) {

        $results = DB::select( DB::raw("SELECT training_schedules.id, courses.course, training_schedules.training_start_date, instructors.full_name,
        COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present,
        COUNT(CASE WHEN training_schedules.training_status = 0 then training_schedules.schedule_id END) as absent,
        COUNT(CASE WHEN training_schedules.training_status = 0 OR training_schedules.training_status = 1 then training_schedules.schedule_id END) as sch_students
  
        FROM training_schedules
        JOIN courses
        ON training_schedules.training_id = courses.id
        JOIN instructors
        ON training_schedules.instructor_id = instructors.id
        WHERE training_schedules.training_start_date >= '$year-$month-01'
        AND training_schedules.training_start_date <= '$year-$month-31'
        AND training_schedules.training_type = 'Internal'
        GROUP BY training_schedules.schedule_id
        ORDER by training_schedules.training_start_date ASC") ); 

    return $results; 


  
        
    }

    public function absentTrainees($year, $month){
        $result = DB::table('training_schedules')
        ->leftJoin('trainees', 'training_schedules.trainee_id', '=', 'trainees.id')
        ->where('training_status', '=', 0)
        ->where('training_schedules.training_start_date', '>=', $year . "-" . $month . "-" . "01")
        ->where('training_schedules.training_start_date', '<=', $year . "-" . $month . "-" . "31")
        ->where('training_schedules.training_type', '=', 'Internal')
        ->OrderBy('training_schedules.training_start_date', 'ASC')
        ->get(['training_schedules.id','trainees.first_name', 'trainees.surname', 'training_schedules.training_title']);

    return $result;
    }

    public function sendToRegistry(Request $request, $id)
    {
        $trainingSchedule = TrainingSchedule::find($id);
        $trainingSchedule->sent_date = $request->sent_date;
        $trainingSchedule->save();


        $result = DB::table('training_schedules')
        ->where('training_status', '=', 1)
        ->leftJoin('trainees', 'training_schedules.trainee_id', '=', 'trainees.id')
        ->orderBy('training_schedules.created_at', 'DESC')
        ->get(['training_schedules.id', 'trainees.first_name', 'trainees.surname', 'training_schedules.training_title', 
        'training_schedules.training_start_date', 'training_schedules.training_end_date', 
        'training_schedules.cost', 'training_schedules.expiry_date', 'training_schedules.sent_date']);
        return $result;
    
    }

    public function AddToEditParticipants(Request $request){
        $schedule_id = $request->schedule_id;
        $trainees = $request->input('trainees');


        $fetch = DB::table('training_schedules')->Distinct()
        ->where('schedule_id', $schedule_id)
        ->get(['schedule_id','venue', 'training_start_date', 'training_end_date', 'instructor_id',
        'cost', 'training_type', 'training_title', 'validity']);

        $venue = $fetch[0]->venue;
        $validity = $fetch[0]->validity;
        $instructor_id = $fetch[0]->instructor_id;
        $training_cost = $fetch[0]->cost;
        $training_title = $fetch[0]->training_title;
        $training_start_date = $fetch[0]->training_start_date;
        $training_end_date = $fetch[0]->training_end_date;
        $training_type = $fetch[0]->training_type;

        foreach($trainees as $trainee){
            $trainingSchedule = new TrainingSchedule();
            $trainingSchedule->schedule_id = $schedule_id;
            $trainingSchedule->instructor_id = $instructor_id;
            $trainingSchedule->trainee_id = $trainee;
            $trainingSchedule->training_title = $training_title;
            $trainingSchedule->training_start_date = $training_start_date;
            $trainingSchedule->training_end_date = $training_end_date;
            $trainingSchedule->training_type = $training_type;
            $trainingSchedule->cost = $training_cost;
            $trainingSchedule->venue = $venue;
            $trainingSchedule->validity = $validity;
            $trainingSchedule->save();
         };

         $result = DB::table('training_schedules')
        ->leftJoin('trainees', 'training_schedules.trainee_id', '=', 'trainees.id')
        ->select('training_schedules.id', 'trainees.surname',  'trainees.staff_id', 'trainees.first_name', 
        'training_schedules.training_status')
        ->where('schedule_id', $schedule_id)
        ->get();

        return $result;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $schedule_id = Str::random(15);
        $trainees = $request->input('trainees');
        $training = $request->training;
        $validity = $request->validity;
        $training_start_date = $request->training_date;
        $training_type = $request->training_type;
        $training_venue = $request->venue;
        $training_cost = $request->cost;
        $training_end_date = $request->end_date;
        $instructor = $request->instructor;


        foreach($trainees as $trainee){
            $trainingSchedule = new TrainingSchedule();
            $trainingSchedule->schedule_id = $schedule_id;
            $trainingSchedule->trainee_id = $trainee;
            $trainingSchedule->training_title = $training;
            $trainingSchedule->instructor_id = $instructor;
            $trainingSchedule->training_start_date = $training_start_date;
            $trainingSchedule->training_end_date = $training_end_date;
            $trainingSchedule->training_type = $training_type;
            $trainingSchedule->cost = $training_cost;
            $trainingSchedule->venue = $training_venue;
            $trainingSchedule->validity = $validity;
            $trainingSchedule->save();
         }

        
    }

    public function changeStatus($id, $schedule_id) {
        $schedule = TrainingSchedule::find($id);
       // $course_id = $schedule->training_id;
       $training_title = $schedule->training_title;
        $training_end_date = $schedule->training_end_date;
      //  $course = Course::where('id', $course_id)->get('validity');
        $validity = $schedule->validity;
        $expiry_date = date('Y-m-d', strtotime( $validity . " " . "months", strtotime($training_end_date)));
        
        if($schedule->training_status == 0){
            if($validity != NULL){
                $schedule->expiry_date = $expiry_date;
                $schedule->training_status = 1;
                $schedule->save();
            }else{
                $schedule->expiry_date = Null;
                $schedule->training_status = 1;
                $schedule->save();
            }
            
        }else{
            $schedule->expiry_date = NULL;
            $schedule->training_status = 0;
            $schedule->save();
        }

        $result = DB::table('training_schedules')
        ->leftJoin('trainees', 'training_schedules.trainee_id', '=', 'trainees.id')
        ->select('training_schedules.id', 'trainees.surname',  'trainees.staff_id', 'trainees.first_name', 
        'training_schedules.training_status')
        ->where('schedule_id', $schedule_id)
        ->get();

        return $result;

       
        
    }

    public function fetchInternalTrainingCostReport($value, $year) {
        //    return($year);
            if($value == 'first'){
            $a_result = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, 
            training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost,
            COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present
      
            FROM training_schedules
            WHERE training_schedules.training_start_date >= '$year-01-01'
            AND training_schedules.training_start_date <= '$year-01-31'
            AND training_schedules.training_type = 'Internal'
    
            GROUP BY training_schedules.schedule_id
            ORDER by training_schedules.training_start_date ASC") ); 
    
            $b_result = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, 
            training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost,
            COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present
    
            FROM training_schedules
            WHERE training_schedules.training_start_date >= '$year-02-01'
            AND training_schedules.training_start_date <= '$year-02-31'
            AND training_schedules.training_type = 'Internal'
            GROUP BY training_schedules.schedule_id
            ORDER by training_schedules.training_start_date ASC") ); 
    
            $c_result = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, 
            training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost,
            COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present
    
            FROM training_schedules
            WHERE training_schedules.training_start_date >= '$year-03-01'
            AND training_schedules.training_start_date <= '$year-03-31'
            AND training_schedules.training_type = 'Internal'
            AND training_schedules.training_status = 1
            ORDER by training_schedules.training_start_date ASC") );
            
            $a_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-01-01' AND training_start_date <= '$year-01-31' 
            AND training_status = 1"));
    
            $a_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-01-01' AND training_start_date <= '$year-01-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $b_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-02-01' AND training_start_date <= '$year-02-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $b_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-02-01' AND training_start_date <= '$year-02-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $c_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-03-01' AND training_start_date <= '$year-03-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $c_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-03-01' AND training_start_date <= '$year-03-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $t_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-01-01' AND training_start_date <= '$year-03-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $t_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-01-01' AND training_start_date <= '$year-03-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            return array('a_result' =>$a_result, 'b_result' => $b_result, 'c_result' => $c_result,
            'a_sum' => $a_sum, 'b_sum' => $b_sum, 'c_sum' => $c_sum, 't_sum' => $t_sum, 
            'a_count' => $a_count, 'b_count' => $b_count, 'c_count' => $c_count, 't_count' => $t_count,); 
    
            
    
            } elseif($value == "second") {
    
            $a_result = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, 
            training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost,
            COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present
      
            FROM training_schedules
            WHERE training_schedules.training_start_date >= '$year-04-01'
            AND training_schedules.training_start_date <= '$year-04-31'
            AND training_schedules.training_type = 'Internal'
            GROUP BY training_schedules.schedule_id
            ORDER by training_schedules.training_start_date ASC") ); 
    
            $b_result = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, 
            training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost,
            COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present
    
            FROM training_schedules
            WHERE training_schedules.training_start_date >= '$year-05-01'
            AND training_schedules.training_start_date <= '$year-05-31'
            AND training_schedules.training_type = 'Internal'
          
            GROUP BY training_schedules.schedule_id
            ORDER by training_schedules.training_start_date ASC") ); 
    
            $c_result = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, 
            training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost,
            COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present
    
            FROM training_schedules
            WHERE training_schedules.training_start_date >= '$year-06-01'
            AND training_schedules.training_start_date <= '$year-06-31'
            AND training_schedules.training_type = 'Internal'
        
            GROUP BY training_schedules.schedule_id
            ORDER by training_schedules.training_start_date ASC") );
            
            $a_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-04-01' AND training_start_date <= '$year-04-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $a_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-04-01' AND training_start_date <= '$year-04-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $b_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-05-01' AND training_start_date <= '$year-05-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $b_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-05-01' AND training_start_date <= '$year-05-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $c_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-06-01' AND training_start_date <= '$year-06-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $c_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-06-01' AND training_start_date <= '$year-06-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $t_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-04-01' AND training_start_date <= '$year-06-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $t_count = DB::select( DB::raw("SELECT (id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-04-01' AND training_start_date <= '$year-06-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            return array('a_result' =>$a_result, 'b_result' => $b_result, 'c_result' => $c_result,
            'a_sum' => $a_sum, 'b_sum' => $b_sum, 'c_sum' => $c_sum, 't_sum' => $t_sum, 
            'a_count' => $a_count, 'b_count' => $b_count, 'c_count' => $c_count, 't_count' => $t_count,); 
    
    
            }elseif($value == 'third') {
                $a_result = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, 
                training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost,
            COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present
      
            FROM training_schedules
            WHERE training_schedules.training_start_date >= '$year-07-01'
            AND training_schedules.training_start_date <= '$year-07-31'
            AND training_schedules.training_type = 'Internal'
        
            GROUP BY training_schedules.schedule_id
            ORDER by training_schedules.training_start_date ASC") ); 
    
            $b_result = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, 
            training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost,
            COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present
    
            FROM training_schedules
            WHERE training_schedules.training_start_date >= '$year-08-01'
            AND training_schedules.training_start_date <= '$year-08-31'
            AND training_schedules.training_type = 'Internal'
      
            GROUP BY training_schedules.schedule_id
            ORDER by training_schedules.training_start_date ASC") ); 
    
            $c_result = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, 
            training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost,
            COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present
    
            FROM training_schedules
            WHERE training_schedules.training_start_date >= '$year-09-01'
            AND training_schedules.training_start_date <= '$year-09-31'
            AND training_schedules.training_type = 'Internal'
        
            GROUP BY training_schedules.schedule_id
            ORDER by training_schedules.training_start_date ASC") );
            
            $a_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-07-01' AND training_start_date <= '$year-07-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $a_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-07-01' AND training_start_date <= '$year-07-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $b_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-08-01' AND training_start_date <= '$year-08-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $b_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-08-01' AND training_start_date <= '$year-08-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $c_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-09-01' AND training_start_date <= '$year-09-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $c_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-09-01' AND training_start_date <= '$year-09-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $t_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-07-01' AND training_start_date <= '$year-09-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $t_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-07-01' AND training_start_date <= '$year-09-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            return array('a_result' =>$a_result, 'b_result' => $b_result, 'c_result' => $c_result,
            'a_sum' => $a_sum, 'b_sum' => $b_sum, 'c_sum' => $c_sum, 't_sum' => $t_sum, 
            'a_count' => $a_count, 'b_count' => $b_count, 'c_count' => $c_count, 't_count' => $t_count,);  
    
    
            } elseif($value == "fourth") {
                
                $a_result = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, 
                training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost,
            COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present
      
            FROM training_schedules
            WHERE training_schedules.training_start_date >= '$year-10-01'
            AND training_schedules.training_start_date <= '$year-10-31'
            AND training_schedules.training_type = 'Internal'
    
            GROUP BY training_schedules.schedule_id
            ORDER by training_schedules.training_start_date ASC") ); 
    
            $b_result = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, 
            training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost,
            COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present
    
            FROM training_schedules
            WHERE training_schedules.training_start_date >= '$year-11-01'
            AND training_schedules.training_start_date <= '$year-11-31'
            AND training_schedules.training_type = 'Internal'
          
            GROUP BY training_schedules.schedule_id
            ORDER by training_schedules.training_start_date ASC") ); 
    
            $c_result = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, 
            training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost,
            COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present
    
            FROM training_schedules
            WHERE training_schedules.training_start_date >= '$year-12-01'
            AND training_schedules.training_start_date <= '$year-12-31'
            AND training_schedules.training_type = 'Internal'
        
            GROUP BY training_schedules.schedule_id
            ORDER by training_schedules.training_start_date ASC") );
            
            $a_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-10-01' AND training_start_date <= '$year-10-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $a_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-10-01' AND training_start_date <= '$year-10-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $b_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-11-01' AND training_start_date <= '$year-11-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $b_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-11-01' AND training_start_date <= '$year-11-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $c_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-12-01' AND training_start_date <= '$year-12-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $c_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-12-01' AND training_start_date <= '$year-12-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $t_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-10-01' AND training_start_date <= '$year-12-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            $t_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-10-01' AND training_start_date <= '$year-12-31' 
            AND training_status = 1 AND training_schedules.training_type = 'Internal'"));
    
            return array('a_result' =>$a_result, 'b_result' => $b_result, 'c_result' => $c_result,
            'a_sum' => $a_sum, 'b_sum' => $b_sum, 'c_sum' => $c_sum, 't_sum' => $t_sum, 
            'a_count' => $a_count, 'b_count' => $b_count, 'c_count' => $c_count, 't_count' => $t_count,); 
    
            }else{
             /*   $a_result = null;
                $b_result = null;
                $c_result = null;
                $a_sum = null;
                $b_sum = null;
                $c_sum = null;
                $t_sum = null;
    
                return array('a_result' =>$a_result, 'b_result' => $b_result, 'c_result' => $c_result,
                'a_sum' => $a_sum, 'b_sum' => $b_sum, 'c_sum' => $c_sum, 't_sum' => $t_sum); */
    
                return array([]);
            }
    
    
      
        }
    

    
    
   
   
    public function fetchInternalTrainingReport($year, $month){

        $results = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, instructors.full_name,
        training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost, 
        COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present, 
        COUNT(CASE WHEN training_schedules.training_status = 0 then training_schedules.schedule_id END) as absent 
        FROM training_schedules 
        JOIN instructors ON training_schedules.instructor_id = instructors.id  
        WHERE training_schedules.training_start_date >= '$year-$month-01' 
        AND training_schedules.training_start_date <= '$year-$month-31' 
        AND training_schedules.training_type = 'Internal' 
        GROUP BY training_schedules.schedule_id 
        ORDER by training_schedules.training_start_date ASC") ); 
    
        return $results; 
        }

    /**
     * Display the specified resource.
     *
     * @param  \App\TrainingSchedule  $trainingSchedule
     * @return \Illuminate\Http\Response
     */
    public function show(TrainingSchedule $trainingSchedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\TrainingSchedule  $trainingSchedule
     * @return \Illuminate\Http\Response
     */
    public function edit(TrainingSchedule $trainingSchedule)
    {
        //
    }


  
    public function fetchExternalTrainingCostReport($value, $year) {
        //    return($year);
            if($value == 'first'){
            $a_result = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, 
            training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost,
            COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present
      
            FROM training_schedules
            WHERE training_schedules.training_start_date >= '$year-01-01'
            AND training_schedules.training_start_date <= '$year-01-31'
            AND training_schedules.training_type = 'External'
    
            GROUP BY training_schedules.schedule_id
            ORDER by training_schedules.training_start_date ASC") ); 
    
            $b_result = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, 
            training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost,
            COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present
    
            FROM training_schedules
            WHERE training_schedules.training_start_date >= '$year-02-01'
            AND training_schedules.training_start_date <= '$year-02-31'
            AND training_schedules.training_type = 'External'
            GROUP BY training_schedules.schedule_id
            ORDER by training_schedules.training_start_date ASC") ); 
    
            $c_result = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, 
            training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost,
            COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present
    
            FROM training_schedules
            WHERE training_schedules.training_start_date >= '$year-03-01'
            AND training_schedules.training_start_date <= '$year-03-31'
            AND training_schedules.training_type = 'External'
            AND training_schedules.training_status = 1
            ORDER by training_schedules.training_start_date ASC") );
            
            $a_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-01-01' AND training_start_date <= '$year-01-31' 
            AND training_status = 1"));
    
            $a_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-01-01' AND training_start_date <= '$year-01-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $b_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-02-01' AND training_start_date <= '$year-02-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $b_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-02-01' AND training_start_date <= '$year-02-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $c_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-03-01' AND training_start_date <= '$year-03-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $c_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-03-01' AND training_start_date <= '$year-03-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $t_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-01-01' AND training_start_date <= '$year-03-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $t_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-01-01' AND training_start_date <= '$year-03-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            return array('a_result' =>$a_result, 'b_result' => $b_result, 'c_result' => $c_result,
            'a_sum' => $a_sum, 'b_sum' => $b_sum, 'c_sum' => $c_sum, 't_sum' => $t_sum, 
            'a_count' => $a_count, 'b_count' => $b_count, 'c_count' => $c_count, 't_count' => $t_count,); 
    
            
    
            } elseif($value == "second") {
    
            $a_result = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, 
            training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost,
            COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present
      
            FROM training_schedules
            WHERE training_schedules.training_start_date >= '$year-04-01'
            AND training_schedules.training_start_date <= '$year-04-31'
            AND training_schedules.training_type = 'External'
            GROUP BY training_schedules.schedule_id
            ORDER by training_schedules.training_start_date ASC") ); 
    
            $b_result = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, 
            training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost,
            COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present
    
            FROM training_schedules
            WHERE training_schedules.training_start_date >= '$year-05-01'
            AND training_schedules.training_start_date <= '$year-05-31'
            AND training_schedules.training_type = 'External'
          
            GROUP BY training_schedules.schedule_id
            ORDER by training_schedules.training_start_date ASC") ); 
    
            $c_result = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, 
            training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost,
            COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present
    
            FROM training_schedules
            WHERE training_schedules.training_start_date >= '$year-06-01'
            AND training_schedules.training_start_date <= '$year-06-31'
            AND training_schedules.training_type = 'External'
        
            GROUP BY training_schedules.schedule_id
            ORDER by training_schedules.training_start_date ASC") );
            
            $a_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-04-01' AND training_start_date <= '$year-04-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $a_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-04-01' AND training_start_date <= '$year-04-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $b_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-05-01' AND training_start_date <= '$year-05-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $b_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-05-01' AND training_start_date <= '$year-05-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $c_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-06-01' AND training_start_date <= '$year-06-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $c_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-06-01' AND training_start_date <= '$year-06-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $t_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-04-01' AND training_start_date <= '$year-06-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $t_count = DB::select( DB::raw("SELECT (id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-04-01' AND training_start_date <= '$year-06-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            return array('a_result' =>$a_result, 'b_result' => $b_result, 'c_result' => $c_result,
            'a_sum' => $a_sum, 'b_sum' => $b_sum, 'c_sum' => $c_sum, 't_sum' => $t_sum, 
            'a_count' => $a_count, 'b_count' => $b_count, 'c_count' => $c_count, 't_count' => $t_count,); 
    
    
            }elseif($value == 'third') {
                $a_result = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, 
                training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost,
            COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present
      
            FROM training_schedules
            WHERE training_schedules.training_start_date >= '$year-07-01'
            AND training_schedules.training_start_date <= '$year-07-31'
            AND training_schedules.training_type = 'External'
        
            GROUP BY training_schedules.schedule_id
            ORDER by training_schedules.training_start_date ASC") ); 
    
            $b_result = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, 
            training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost,
            COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present
    
            FROM training_schedules
            WHERE training_schedules.training_start_date >= '$year-08-01'
            AND training_schedules.training_start_date <= '$year-08-31'
            AND training_schedules.training_type = 'External'
      
            GROUP BY training_schedules.schedule_id
            ORDER by training_schedules.training_start_date ASC") ); 
    
            $c_result = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, 
            training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost,
            COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present
    
            FROM training_schedules
            WHERE training_schedules.training_start_date >= '$year-09-01'
            AND training_schedules.training_start_date <= '$year-09-31'
            AND training_schedules.training_type = 'External'
        
            GROUP BY training_schedules.schedule_id
            ORDER by training_schedules.training_start_date ASC") );
            
            $a_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-07-01' AND training_start_date <= '$year-07-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $a_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-07-01' AND training_start_date <= '$year-07-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $b_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-08-01' AND training_start_date <= '$year-08-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $b_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-08-01' AND training_start_date <= '$year-08-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $c_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-09-01' AND training_start_date <= '$year-09-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $c_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-09-01' AND training_start_date <= '$year-09-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $t_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-07-01' AND training_start_date <= '$year-09-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $t_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-07-01' AND training_start_date <= '$year-09-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            return array('a_result' =>$a_result, 'b_result' => $b_result, 'c_result' => $c_result,
            'a_sum' => $a_sum, 'b_sum' => $b_sum, 'c_sum' => $c_sum, 't_sum' => $t_sum, 
            'a_count' => $a_count, 'b_count' => $b_count, 'c_count' => $c_count, 't_count' => $t_count,);  
    
    
            } elseif($value == "fourth") {
                
                $a_result = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, 
                training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost,
            COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present
      
            FROM training_schedules
            WHERE training_schedules.training_start_date >= '$year-10-01'
            AND training_schedules.training_start_date <= '$year-10-31'
            AND training_schedules.training_type = 'External'
    
            GROUP BY training_schedules.schedule_id
            ORDER by training_schedules.training_start_date ASC") ); 
    
            $b_result = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, 
            training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost,
            COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present
    
            FROM training_schedules
            WHERE training_schedules.training_start_date >= '$year-11-01'
            AND training_schedules.training_start_date <= '$year-11-31'
            AND training_schedules.training_type = 'External'
          
            GROUP BY training_schedules.schedule_id
            ORDER by training_schedules.training_start_date ASC") ); 
    
            $c_result = DB::select( DB::raw("SELECT training_schedules.id, training_schedules.training_title, 
            training_schedules.training_start_date, training_schedules.training_end_date, training_schedules.cost,
            COUNT(CASE WHEN training_schedules.training_status = 1 then training_schedules.schedule_id END) as present
    
            FROM training_schedules
            WHERE training_schedules.training_start_date >= '$year-12-01'
            AND training_schedules.training_start_date <= '$year-12-31'
            AND training_schedules.training_type = 'External'
        
            GROUP BY training_schedules.schedule_id
            ORDER by training_schedules.training_start_date ASC") );
            
            $a_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-10-01' AND training_start_date <= '$year-10-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $a_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-10-01' AND training_start_date <= '$year-10-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $b_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-11-01' AND training_start_date <= '$year-11-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $b_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-11-01' AND training_start_date <= '$year-11-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $c_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-12-01' AND training_start_date <= '$year-12-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $c_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-12-01' AND training_start_date <= '$year-12-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $t_sum = DB::select( DB::raw("SELECT SUM(cost) AS cost FROM training_schedules WHERE 
            training_start_date >= '$year-10-01' AND training_start_date <= '$year-12-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            $t_count = DB::select( DB::raw("SELECT COUNT(id) AS attendee FROM training_schedules WHERE 
            training_start_date >= '$year-10-01' AND training_start_date <= '$year-12-31' 
            AND training_status = 1 AND training_schedules.training_type = 'External'"));
    
            return array('a_result' =>$a_result, 'b_result' => $b_result, 'c_result' => $c_result,
            'a_sum' => $a_sum, 'b_sum' => $b_sum, 'c_sum' => $c_sum, 't_sum' => $t_sum, 
            'a_count' => $a_count, 'b_count' => $b_count, 'c_count' => $c_count, 't_count' => $t_count,); 
    
            }else{
             /*   $a_result = null;
                $b_result = null;
                $c_result = null;
                $a_sum = null;
                $b_sum = null;
                $c_sum = null;
                $t_sum = null;
    
                return array('a_result' =>$a_result, 'b_result' => $b_result, 'c_result' => $c_result,
                'a_sum' => $a_sum, 'b_sum' => $b_sum, 'c_sum' => $c_sum, 't_sum' => $t_sum); */
    
                return array([]);
            }
    
    
      
        }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\TrainingSchedule  $trainingSchedule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $schedule_id)
    {

        $training_start_date = $request->training_date;
        $training_type = $request->training_type;
        $training_venue = $request->venue;
        $training_cost = $request->cost;
        $validity = $request->validity;

        $training_end_date = $request->end_date;
        $training_title = $request->training_title;

        $update = DB::table('training_schedules')
              ->where('schedule_id', $schedule_id)
              ->update(['training_start_date' => $training_start_date,
                        'training_end_date'=>$training_end_date,
                        'training_title'=>$training_title,
                        'venue'=>$training_venue,
                        'validity' => $validity,
                        'training_type'=>$training_type,
                        'cost'=>$training_cost]);

   
        $result = DB::table('training_schedules')->Distinct()
        ->leftJoin('instructors', 'training_schedules.instructor_id', '=', 'instructors.id')
        ->orderBy('training_schedules.created_at', 'DESC')
        ->get(['schedule_id', 'training_title', 'full_name', 'venue', 'training_start_date', 'instructor_id', 'training_end_date',
        'cost', 'training_type', 'validity']);

        return $result;

    }

    function expirationReminder() {
        
        $year = date('Y');
        $month = date('m');
        $nextMonth = date('m') +1;
        

        $thisMonthExpiry =  DB::table('training_schedules')
        ->leftJoin('trainees', 'training_schedules.trainee_id', '=', 'trainees.id')
        ->where('training_status', '=', 1)
        ->where('training_schedules.expiry_date', '>=', $year . "-" . $month . "-" . "01")
        ->where('training_schedules.expiry_date', '<=', $year . "-" . $month . "-" . "31")
        ->OrderBy('training_schedules.expiry_date', 'ASC')
        ->get(['training_schedules.id','trainees.first_name', 'trainees.surname', 
        'training_schedules.training_title']);

        $nextMonthExpiry =  DB::table('training_schedules')
        ->leftJoin('trainees', 'training_schedules.trainee_id', '=', 'trainees.id')
        ->where('training_status', '=', 1)
        ->where('training_schedules.expiry_date', '>=', $year . "-" . $nextMonth . "-" . "01")
        ->where('training_schedules.expiry_date', '<=', $year . "-" . $nextMonth . "-" . "31")
        ->OrderBy('training_schedules.expiry_date', 'ASC')
        ->get(['training_schedules.id','trainees.first_name', 'trainees.surname', 
        'training_schedules.training_title']);

         return array('this_month_expiry'=> $thisMonthExpiry, 'next_month_expiry'=>$nextMonthExpiry);
    }

    public function deleteParticipant($id, $schedule_id)
    {
        TrainingSchedule::find($id)->delete();

        $result = DB::table('training_schedules')
        ->leftJoin('trainees', 'training_schedules.trainee_id', '=', 'trainees.id')
        ->select('training_schedules.id', 'trainees.surname',  'trainees.staff_id', 'trainees.first_name', 
        'training_schedules.training_status')
        ->where('schedule_id', $schedule_id)
        ->get();

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\TrainingSchedule  $trainingSchedule
     * @return \Illuminate\Http\Response
     */
    public function destroy($schedule_id)
    {
        DB::table('training_schedules')->where('schedule_id', $schedule_id)->delete();


        $result = DB::table('training_schedules')->Distinct()
        ->leftJoin('instructors', 'training_schedules.instructor_id', '=', 'instructors.id')
        ->orderBy('training_schedules.created_at', 'DESC')
        ->get(['schedule_id', 'training_title', 'full_name', 'venue', 'training_start_date', 'instructor_id', 'training_end_date',
        'cost', 'training_type', 'validity']);
    }
}

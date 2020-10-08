<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Trainee;
use App\TrainingSchedule;
use App\Course;
use DB;


class TraineeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = Trainee::where('status', '=', 1)->orderBy('id','DESC')->get();
        return $result;
    }

    public function exited()
    {
        $result = Trainee::where('status', '=', 0)->orderBy('id','DESC')->get();
        return $result;
    }

    public function loopParticipant($schedule_id) {

       // $schedule_id = 'oT03ovM2oUxmXec';
            
           $result = DB::table("trainees")->select('*')->whereNotIn('id', function($query) use ($schedule_id) {
           
         //   $schedule_id = $schedule_id;

              $query->select('trainee_id')->from('training_schedules')
              ->where('schedule_id', '=', $schedule_id);
           
           })->get();
     
        return $result;

    }

    public function counts() {
        $active_trainee_count = DB::table('trainees')->where('status', '=', 1)->count();

        $exited_trainee_count = DB::table('trainees')->where('status', '=', 0)->count();

        $instructor_count = DB::table('instructors')->count();

        return array('active_trainee_count'=>$active_trainee_count, 
                    'exited_trainee_count'=>$exited_trainee_count, 'instructor_count'=>$instructor_count);
    }


    public function test(){
        return "test...";
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $trainee = new Trainee();
        $trainee->staff_id = $request->staff_id;
        $trainee->first_name = $request->first_name;
        $trainee->surname = $request->surname;
        $trainee->save();
        $result = Trainee::where('status', '=', 1)->orderBy('id','DESC')->get();
        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      
        $result = DB::table('training_schedules')
        ->where('training_status', '=', 1)
        ->where('trainee_id', '=', $id)
        ->leftJoin('trainees', 'training_schedules.trainee_id', '=', 'trainees.id')
        ->orderBy('training_schedules.id', 'DESC')
        ->get(['training_schedules.training_start_date', 'training_schedules.training_title', 'training_schedules.training_end_date',
        'trainees.first_name', 'trainees.surname','training_schedules.cost', 'training_schedules.expiry_date']);
        return $result;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
 

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $trainee = Trainee::find($id);
        $trainee->staff_id = $request->staff_id;
        $trainee->first_name = $request->first_name;
        $trainee->surname = $request->surname;
        $trainee->save();
        $result = Trainee::where('status', '=', 1)->orderBy('id','DESC')->get();
        return $result;
    }

    public function exit($id)
    {
        $trainee = Trainee::find($id);
        $trainee->status = 0;
        $trainee->save();
        $result = Trainee::where('status', '=', 1)->orderBy('id','DESC')->get();
        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Trainee::find($id)->delete();
        $result = Trainee::where('status', '=', 1)->orderBy('id','DESC')->get();
        return $result;
    }
}

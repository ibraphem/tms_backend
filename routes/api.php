<?php

use Illuminate\Http\Request;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});



Route::group(['middleware' => ['jwt.auth','api-header']], function () {
  
    // all routes to protected resources are registered here  
    Route::get('users/list', function(){
        $users = App\User::all();
        
        $response = ['success'=>true, 'data'=>$users];
        return response()->json($response, 201);
    });
});
Route::group(['middleware' => 'api-header'], function () {
  
    // The registration and login requests doesn't come with tokens 
    // as users at that point have not been authenticated yet
    // Therefore the jwtMiddleware will be exclusive of them

Route::post('user/login', 'UserController@login');

Route::post('user/register', 'UserController@register');

Route::get('trainee', 'api\TraineeController@index');

Route::get('count', 'api\TraineeController@counts');

Route::get('exitedtrainee', 'api\TraineeController@exited');

Route::post('trainee/store', 'api\TraineeController@store');

Route::put('trainee/update/{id}', 'api\TraineeController@update');

Route::put('trainee/exit/{id}', 'api\TraineeController@exit');

Route::put('trainee/active/{id}', 'api\TraineeController@active');

Route::delete('trainee/delete/{id}', 'api\TraineeController@destroy');

Route::get('courses', 'api\CourseController@index');

Route::post('course/store', 'api\CourseController@store');

Route::put('course/update/{id}', 'api\CourseController@update');

Route::delete('course/delete/{id}', 'api\CourseController@destroy');

Route::get('trainingschedule', 'api\TrainingScheduleController@index');

Route::post('trainingschedule/store', 'api\TrainingScheduleController@store');

Route::get('trainingschedule/participants/{schedule_id}', 'api\TrainingScheduleController@fetchParticipants');

Route::put('schedule/{id}/{schedule_id}', 'api\TrainingScheduleController@changeStatus');

Route::put('sendcertificate/{id}', 'api\TrainingScheduleController@sendToRegistry');

Route::delete('schedule/delete/{schedule_id}', 'api\TrainingScheduleController@destroy');

Route::put('schedul/update/{schedule_id}', 'api\TrainingScheduleController@update');

Route::delete('participant/{id}/{schedule_id}', 'api\TrainingScheduleController@deleteParticipant');

Route::post('scheduleparticipant/add', 'api\TrainingScheduleController@AddToEditParticipants');

Route::get('addparticipant/{schedule_id}', 'api\TraineeController@loopParticipant');

Route::get('trainingrecords', 'api\TrainingScheduleController@fetchCompletedTrainings');

Route::get('showtrainings/{id}', 'api\TrainingScheduleController@showCert');

Route::get('instructor', 'api\InstructorController@index');

Route::post('instructor/store', 'api\InstructorController@store');

Route::put('instructor/update/{id}', 'api\InstructorController@update');

Route::get('instructorcourses/{id}', 'api\InstructorCourseController@index');

Route::post('instructorcourse/store', 'api\InstructorCourseController@store');

Route::put('instructorcourse/update/{id}', 'api\InstructorCourseController@update');

Route::get('internal/report/{year}/{month}', 'api\TrainingScheduleController@fetchInternalTrainingReport');

Route::get('internal/report/cost/{value}/{year}', 'api\TrainingScheduleController@fetchInternalTrainingCostReport');

Route::get('external/report/cost/{value}/{year}', 'api\TrainingScheduleController@fetchExternalTrainingCostReport');

Route::get('absent/trainees/{year}/{month}', 'api\TrainingScheduleController@absentTrainees');

Route::get('expiry', 'api\TrainingScheduleController@expirationReminder');

Route::get('thismonthtraining', 'api\TrainingScheduleController@thisMonthTrainings');

Route::get('test/{id}', 'api\TrainingScheduleController@test');

Route::get('trainingrequest', 'api\TrainingRequestController@index');

Route::post('request/store', 'api\TrainingRequestController@store');

Route::put('request/update/{id}', 'api\TrainingRequestController@update');
});
// Route::get('addpartticipant/{schedule_id}', 'api\TraineeController@loopParticipant');

// Route::get('user/test', 'UserController@test');
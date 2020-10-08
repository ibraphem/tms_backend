<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\InstructorCourse;
use Illuminate\Http\Request;
use DB;

class InstructorCourseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $result = InstructorCourse::where('instructor_id', '=', $id)->get();
        return $result;
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
       
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $InstructorCourse = new InstructorCourse();
        $InstructorCourse->instructor_id = $request->instructor_id;
        $InstructorCourse->course_title = $request->course_title;
        $InstructorCourse->licence_expiry_date = $request->licence_expiry_date;
        $InstructorCourse->save();
        $result = InstructorCourse::where('instructor_id', '=', $request->instructor_id)->get();
        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\InstructorCourse  $instructorCourse
     * @return \Illuminate\Http\Response
     */
    public function show(InstructorCourse $instructorCourse)
    {
        //
    }

   
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\InstructorCourse  $instructorCourse
     * @return \Illuminate\Http\Response
     */
    public function edit(InstructorCourse $instructorCourse)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\InstructorCourse  $instructorCourse
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $InstructorCourse = InstructorCourse::find($id);
        $InstructorCourse->course_title = $request->course_title;
        $InstructorCourse->licence_expiry_date = $request->licence_expiry_date;
        $InstructorCourse->save();
        $result = InstructorCourse::where('instructor_id', '=', $request->instructor_id)->get();
        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\InstructorCourse  $instructorCourse
     * @return \Illuminate\Http\Response
     */
    public function destroy(InstructorCourse $instructorCourse)
    {
        //
    }
}

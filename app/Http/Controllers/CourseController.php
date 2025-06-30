<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Objective;
use App\Models\User;

class CourseController extends Controller
{
    public function myCourses()
    {
        $user = auth()->user();
        if($user->hasRole('teacher')){
            $courses = Course::where('instructor_id', auth()->id())->get()->map(function ($course) {
                return $course->format();
            });
        }
        if($user->hasRole('student')){
            $courses = $user->courses->map(function ($course) {
                return $course->format();
            });
        }

        return response()->json([
            'courses' => $courses
        ]);
    }


    public function store(Request $request)
    {
        $attrs = $request->validate([
            'title_fr' => 'sometimes|max:100',
            'title_en' => 'sometimes|max:100'
        ]);

        $joinCode = strtoupper(bin2hex(random_bytes(3)));
        while (Course::where('join_code', $joinCode)->exists()) {
            $joinCode = strtoupper(bin2hex(random_bytes(3)));
        }

        $course = Course::create([
            'title_fr' => $attrs['title_fr'],
            'title_en' => $attrs['title_en'],
            'description' => ['fr' => '', 'en' => ''],
            'rewards' => [
                'objectives' => ['blue' => 0.6, 'green' => 0.9],
                'levels' => [
                    ['name' => ['fr' => 'Débutant', 'en' => 'Beginner'], 'points' => 20],
                    ['name' => ['fr' => 'Avancé', 'en' => 'Confirmed'], 'points' => 50],
                    ['name' => ['fr' => 'Expert', 'en' => 'Expert'], 'points' => 100]
                ],
            ],
            'join_code' => $joinCode,
            'sections' => [],
            'instructor_id' => auth()->id()
        ]);
           
        return response()->json([
            'course' => $course->format(),
            'message' => [
                'text' => 'Course created',
                'type' => 'success'
            ]
        ]);
    }

    public function show(Course $course)
    {
        return response()->json([
            'course' => $course->format()
        ]);
    }

    public function update(Course $course, Request $request)
    {
        $attrs = $request->validate([
            'description' => 'required|array',
            'description.en' => 'nullable|string',
            'description.fr' => 'nullable|string',
            'title' => 'required|array',
            'title.en' => 'nullable|string',
            'title.fr' => 'nullable|string',
            'sections' => 'required|array',
            'rewards' => 'required|array'
        ]);

        $sections = [];
        foreach($attrs['sections'] as $section){
            $objectives = [];
            foreach($section['objectives'] as $objective){
                if(isset($objective['new'])){
                    $dbobjective = Objective::create([
                        'title_fr' => $objective['title']['fr'],
                        'title_en' => $objective['title']['en'],
                        'description' => $objective['description'],
                        'points' => $objective['points'],
                        'course_id' => $course['id']
                    ]);
                } else {
                    $dbobjective = Objective::find($objective['id']);
                    $dbobjective->update([
                        'title_fr' => $objective['title']['fr'],
                        'title_en' => $objective['title']['en'],
                        'description' => $objective['description'],
                        'points' => $objective['points']
                    ]);
                }
                $objectives[] = $dbobjective->id;
            }
            $sections[] = [
                'title_fr' => $section['title']['fr'],
                'title_en' => $section['title']['en'],
                'description' => $section['description'],
                'objectives' => $objectives
            ];
        }

        $course->update([
            'title_fr' => $attrs['title']['fr'],
            'title_en' => $attrs['title']['en'],
            'description' => $attrs['description'],
            'sections' => $sections,
            'rewards' => $attrs['rewards']
        ]);

        return response()->json([
            'course' => $course->format()
        ]);
    }

    public function searchStudent(Course $course, Request $request)
    {
        $name = ($request->validate(['name' => 'required|String|min:3|max:100']))['name'];

        $students = [];
        foreach(User::role('student')->where('name', 'like', "%$name%")->get() as $student){
            if(!$student->courses()->where('course_id', $course->id)->exists()){
                $students[] = [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                ];
            }
            if(count($students) > 4) break;
        }

        return response()->json(['students' => $students]);
    }

    public function addStudent(Course $course, Request $request)
    {
        $attrs = $request->validate([
            'id' => 'required|Integer'
        ]);

        $course->students()->attach($attrs['id']);

        $user = User::find($attrs['id']);

        return response()->json([
            'course' => $course->format(),
            'message' => [
                    'text' => 'Student added',
                    'type' => 'success'
                ]
        ]);
    }

    public function updateScores(Course $course, Request $request)
    {
        $attrs = $request->validate([
            'studentId' => 'required|Integer',
            'newValues' => 'required|Array'
        ]);
        $user = User::find(intval($attrs['studentId']));
        if(!$user){
            return response()->json([
                'message' => [
                        'text' => 'Student not found',
                        'type' => 'error'
                    ]
            ]);
        }

        foreach($attrs['newValues'] as $newScore){
            $updated = $user->objectives()->updateExistingPivot($newScore['id'], ['score' => $newScore['score']]);
            if (!$updated) {
                $user->objectives()->attach($newScore['id'], ['score' => $newScore['score']]);
            }
            // $obj = $user->objectives()->where('objective_id', $newScore['id'])->firstOrCreate();
            // $obj->updateExistingPivot()
        }

        return response()->json([
            'course' => $course->format(),
            'message' => [
                    'text' => 'Scores updated',
                    'type' => 'success'
                ]
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Objective;
use App\Models\User;
use App\Models\Bonus;

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

    public function removeStudent(Course $course, Request $request)
    {
        $attrs = $request->validate(['list' => 'required|Array']);

        foreach($attrs['list'] as $id){
            $course->students()->detach($id);
        }

        $msg = count($attrs['list']) > 1 ? 'Students removed' : 'Student removed';

        return response()->json([
            'course' => $course->format(),
            'message' => [
                    'text' => $msg,
                    'type' => 'info'
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

    public function join(Request $request)
    {
        $attrs = $request->validate(['code' => 'required|String|max:6|min:6']);

        $course = Course::where('join_code', $attrs['code'])->first();
        if(!$course){
            return response()->json([
                'message' => [
                        'text' => 'Course not found !',
                        'type' => 'warning'
                    ]
            ]);
        }
        
        $user = auth()->user();

        if($course->students->contains($user)) {
            return response()->json([
                'message' => [
                        'text' => 'You already joined this course',
                        'type' => 'info'
                    ]
            ]);
        }

        if($user->hasRole('student')){
            $course->students()->attach($user);
            return response()->json([
                'course' => $course->format(),
                'message' => [
                        'text' => 'You joined the course !',
                        'type' => 'success'
                    ]
            ]);
        }

        return response()->json([
            'message' => [
                    'text' => 'You cannot join this course',
                    'type' => 'warning'
                ]
        ]);

    }

    public function leave(Course $course)
    {
        $course->students()->detach(auth()->user());
            return response()->json([
                'message' => [
                        'text' => 'You left the course !',
                        'type' => 'info'
                    ]
            ]);
    }

    public function addBonus(Course $course, User $user, Request $request)
    {
        $attrs = $request->validate([
            'description' => 'required|max:100',
            'score' => 'required|max:100'
        ]);

        Bonus::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'description' => $attrs['description'],
            'score' => intval($attrs['score']),
            'instructor_id' => auth()->id()
        ]);

        return response()->json([
            'course' => $course->format(),
            'message' => [
                    'text' => 'Bonus added',
                    'type' => 'success'
                ]
        ]);
    }

    public function editBonus(Course $course, Bonus $bonus, Request $request)
    {
        $attrs = $request->validate([
            'description' => 'required|max:100',
            'score' => 'required|max:100'
        ]);

        $bonus->update($attrs);

        return response()->json([
            'course' => $course->format(),
            'message' => [
                    'text' => 'Bonus updated',
                    'type' => 'success'
                ]
        ]);
    }

    public function deleteBonus(Course $course, Bonus $bonus)
    {
        $bonus->delete();

        return response()->json([
            'course' => $course->format(),
            'message' => [
                    'text' => 'Bonus deleted',
                    'type' => 'success'
                ]
        ]);
    }
}

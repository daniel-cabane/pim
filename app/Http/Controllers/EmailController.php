<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Email;
use App\Mail\WorkshopCommunication;
use App\Models\User;

class EmailController extends Controller
{
    public function update(Email $email, Request $request)
    {
        $attrs = $request->validate([
            'data' => 'required',
            'language' => 'required|String|max:5',
            'subject_fr' => 'required|String|max:100',
            'subject_en' => 'required|String|max:100',
        ]);

        $email->update($attrs);

        return response()->json([
            'email' => $email,
            'message' => [
                    'text' => 'Email updated',
                    'type' => 'success'
                ]
        ]);
    }

    public function updateSchedule(Email $email, Request $request)
    {
        $attrs = $request->validate(['dateTime' => 'sometimes|date_format:Y-m-d H:i:s|nullable']);

        $email->update(['schedule' => $attrs['dateTime']]);

        return response()->json([
            'email' => $email,
            'message' => [
                    'text' => 'Schedule updated',
                    'type' => 'success'
                ]
        ]);
    }

    public function preview(Email $email)
    {
        return response()->json(['preview' => (new WorkshopCommunication($email))->render()]);
    }

    public function send(Email $email)
    {
        $email->send();

        return response()->json([
            'email' => $email,
            'message' => [
                    'text' => 'Email sent',
                    'type' => 'success'
                ]
        ]);
    }

    public function sentTo(Email $email)
    {
        $sentTo = [];
        foreach($email->data->sentTo as $id){
            $user = User::find($id);
            $sentTo[] = [
                'id' => $user->id,
                'name' => $user->name,
                'className' => $user->className,
                'email' => $user->email
            ];
        }

        return response()->json(['sentTo' => $sentTo]);
    }

    public function destroy(Email $email)
    {
        $id = $email->id;

        $email->delete();

        return response()->json([
            'id' => $id,
            'message' => [
                    'text' => 'Email deleted',
                    'type' => 'info'
                ]
        ]);
    }
}

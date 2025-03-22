<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;

class MessageController extends Controller
{
    public function sendToAdmin(Request $request)
    {
        $msg = $request->validate(['msg' => 'required|String|max:500'])['msg'];

        Message::create([
            'from' => auth()->id(),
            'to' => 1,
            'body' => $msg
        ]);

        return response()->json([
            'message' => [
                'text' => 'Message sent',
                'type' => 'success'
            ]
        ]);
    }
}

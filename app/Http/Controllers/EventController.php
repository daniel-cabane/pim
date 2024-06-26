<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Holiday;

class EventController extends Controller
{
    public function holidays()
    {
        return response()->json(['holidays' => Holiday::all()]);
    }
}

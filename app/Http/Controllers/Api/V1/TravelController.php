<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TravelResource;
use Illuminate\Http\Request;
use App\Models\Travel;

class TravelController extends Controller
{
    public function index() {
        $travels = Travel::where('is_public', true)->paginate(16);
        return TravelResource::collection($travels);
    }
}

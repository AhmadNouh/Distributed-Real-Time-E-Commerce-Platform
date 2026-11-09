<?php

namespace App\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use App\Traits\ApiResponse;

abstract class BaseApiController extends Controller
{
    use AuthorizesRequests, ValidatesRequests, ApiResponse;
}

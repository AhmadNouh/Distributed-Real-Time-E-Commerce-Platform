<?php
namespace App\Modules\Notifications\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class NotificationController extends Controller
{
    public function index()
    {
        return Auth::user()->notifications;
    }
    public function read($id)
    {
        $n = Auth::user()->notifications()->findOrFail($id);
        $n->markAsRead();
        return response()->json(['message'=>'Read']);
    }
}

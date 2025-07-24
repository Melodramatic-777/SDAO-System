<?php
// In app/Http/Controllers/ActivitiesAdminController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ActivitiesAdminController extends Controller
{
    public function home()
    {
        return view('activities-admin.home');
    }
}

?>
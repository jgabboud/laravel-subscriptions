<?php

namespace Jgabboud\Subscriptions\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Jgabboud\Subscriptions\Models\Plan;

class PlanController extends Controller
{
    public function get($id){
        return Plan::find($id);
    }

    public function subtract($a, $b){
        echo $a - $b;
    }
}

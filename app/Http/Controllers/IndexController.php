<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ContactRequest;

class IndexController extends Controller
{
    
    // トップページの表示
    public function home()
    {
        return view('home');
    }

}

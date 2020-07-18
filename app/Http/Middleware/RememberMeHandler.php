<?php

// namespace App\Http\Middleware;

// use Closure;

// use Illuminate\Support\Facades\Auth; // ★追加
// use Illuminate\Support\Facades\Log; // ★追加

// class RememberMeHandler
// {
//     /**
//      * Handle an incoming request.
//      *
//      * @param  \Illuminate\Http\Request  $request
//      * @param  \Closure  $next
//      * @return mixed
//      */
//     public function handle($request, Closure $next)
//     {   
//         if(Auth::viaRemember()){ // Remember Meでの認証時
//             Log::notice('remember meクッキーを使用して認証されています');
//             // 行いたい処理を書く
//         }
//         return $next($request);
//     }
// }

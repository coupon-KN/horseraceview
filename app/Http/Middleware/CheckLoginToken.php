<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Storage;
use DateTime;

class CheckLoginToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie("chestnut-token");
        if(!empty($token)){
            // トークンチェック
            if (Storage::disk("public")->exists("token.json")) {
                $json = json_decode(Storage::disk("public")->get("token.json"), true);
                if($token == $json["token"]){
                    $dtNow = new DateTime();
                    $dtLimit = new DateTime($json["limit"]);
                    if($dtLimit >= $dtNow){
                        return $next($request);
                    }
                }
            }
        }

        return response(["id" => "", "name" => ""], 401);
    }
}

<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


/**
 * ログインコントローラ
 */
class LoginController extends Controller
{
    /**
     * 初期表示
     */
    function index() {
        return view("login", []);
    }

    /**
     * ログイン処理
     */
    function login(Request $request) {
        $input = $request->all();
        $userId = hash('sha256', $input["user_id"]);
        $passWd = hash('sha256', $input["password"]);

        $users = json_decode(Storage::disk("public")->get("users.json"), true);
        foreach($users as $val){
            if($userId == $val["user_id"] && $passWd == $val["password"]){
                $request->session()->put(config("const.SESSION_LOGIN_USER"), $val["name"]);
                if($val["admin"]){
                    $request->session()->put(config("const.SESSION_ADMIN"), "1");
                }
                return redirect()->route('calendar.index'); 
            }
        }
        
        return redirect()->route('login'); 
    }

    /**
     * ログアウト処理
     */
    function logout(Request $request) {
        $request->session()->flush();
        return redirect()->route('login'); 
    }

}

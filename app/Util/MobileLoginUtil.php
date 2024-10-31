<?php
namespace App\Util;
use Illuminate\Support\Facades\Storage;
use DateTime;
use Ramsey\Uuid\Uuid;


/**
 * モバイル用のログイン管理クラス
 */
class MobileLoginUtil
{
    /**
     * ログイン処理
     */
    public static function login($user, $pass) : string {
        $rtnToken = "";

        $hashUser = hash('sha256', $user);
        $hashPass = hash('sha256', $pass);
        $tokenArr = json_decode(Storage::disk("public")->get("token.json"), true);
        $userArr = json_decode(Storage::disk("public")->get("users.json"), true);
        foreach($userArr as $val){
            if($hashUser == $val["login_id"] && $hashPass == $val["password"]){
                $rtnToken = (string) Uuid::uuid4();
                $tokenArr[$rtnToken] = array(
                    "login_id" => $hashUser,
                    "limit" => (new DateTime('+2 hour'))->format("Y-m-d H:i:s")
                );
                break;
            }
        }
        // 不要なデータを削除
        MobileLoginUtil::deleteOverLimitToken($tokenArr);

        // 書き込み
        $contents = json_encode($tokenArr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        Storage::disk('public')->put("token.json",  $contents);

        return $rtnToken;
    }

    /**
     * ログイン状態を確認
     */
    public static function isLogin($token) : bool {
        $rtnFlg = false;

        $tokenArr = json_decode(Storage::disk("public")->get("token.json"), true);
        if (array_key_exists($token, $tokenArr)) {
            $dtNow = new DateTime();
            $dtLimit = new DateTime($tokenArr[$token]["limit"]);
            if($dtNow <= $dtLimit){
                $tokenArr[$token]["limit"] = (new DateTime('+2 hour'))->format("Y-m-d H:i:s");
                $rtnFlg = true;
            }
        }

        // 書き込み
        $contents = json_encode($tokenArr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        Storage::disk('public')->put("token.json",  $contents);

        return $rtnFlg;
    }

    /**
     * 不要なトークンデータの削除
     */
    public static function deleteOverLimitToken(&$tokenArr) {
        $delKeys = [];
        $dtNow = new DateTime();
        foreach($tokenArr as $key => $val){
            $dtLimit = new DateTime($val["limit"]);
            if($dtLimit < $dtNow){
                $delKeys[] = $key;
            }
        }
        foreach($delKeys as $val){
            unset($tokenArr[$val]);
        }
    }

    /**
     * ログインユーザ情報を取得
     */
    public static function getLoginUserInfo($token) {
        $rtnArr = null;
        $tokenArr = json_decode(Storage::disk("public")->get("token.json"), true);
        if (array_key_exists($token, $tokenArr)) {
            $userId = $tokenArr[$token]["login_id"];

            $userArr = json_decode(Storage::disk("public")->get("users.json"), true);
            foreach($userArr as $val){
                if($val["login_id"] == $userId){
                    $rtnArr = $val;
                    break;
                }
            }
        }

        return $rtnArr;
    }

}

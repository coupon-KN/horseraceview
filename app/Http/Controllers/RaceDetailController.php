<?php
namespace App\Http\Controllers;
use App\Util\NetkeibaUtil;
use App\Util\HorseraceScoringUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


/**
 * レース詳細
 */
class RaceDetailController extends Controller
{
    /**
     * 初期表示
     */
    function index($raceId)
    {
        $viewData["raceid"] = $raceId;
        $viewData["pageTitle"] = "レース詳細";
        $viewData["menuTitle"] = "レース詳細";

        // レースIDを取得
        if (NetkeibaUtil::existsRaceData($raceId)) {
            $raceObj = NetkeibaUtil::GetRaceData($raceId);

            $babaCode = substr($raceId, 4, 2);
            $raceNo = intval(substr($raceId, -2));
            $viewData["pageTitle"] = config("const.BAMEI_NAME")[$babaCode] . $raceNo . "R " . $raceObj->raceName;
            $viewData["menuTitle"] = config("const.BAMEI_NAME")[$babaCode] . $raceNo . "R " . $raceObj->raceName;
            $viewData["centralFlg"] = (in_array($babaCode, config("const.CENTRAL_BAMEI_CODE")));

            // ユーザのメモ情報を取得
            if(session()->exists(config("const.SESSION_LOGIN_USER_NO"))){
                $loginUserNo = session(config("const.SESSION_LOGIN_USER_NO"));
                if (Storage::disk('public')->exists("user_data/" . $loginUserNo . ".json")) {
                    $userData = json_decode(Storage::disk('public')->get("user_data/" . $loginUserNo . ".json"), true);
                    if(array_key_exists($raceId, $userData)) {
                        for($i=0; $i<count($raceObj->horseArray); $i++) {
                            $horse = $raceObj->horseArray[$i];
                            if(array_key_exists($horse->horseId, $userData[$raceId])) {
                                $horse->userComment = $userData[$raceId][$horse->horseId]["comment"];
                            }
                        }
                    }
                }
            }
        }else{
            $raceObj = null;
        }
        $viewData["info"] = $raceObj;

        return view("race-detail", $viewData);
    }

    /**
     * スコア計算
     */
    function scoring($raceId) {
        $rtnArr = HorseraceScoringUtil::scoring($raceId);
        return response($rtnArr, 200);
    }

    /**
     * コメントの書き込み
     */
    function writeComment(Request $request) {
        if(!session()->exists(config("const.SESSION_LOGIN_USER_NO"))){
            return;
        }
        $loginUserNo = session(config("const.SESSION_LOGIN_USER_NO"));

        $input = $request->all();
        if(!array_key_exists("race_id", $input) && !array_key_exists("horse_id", $input) && !array_key_exists("comment", $input)) {
            return;
        }
        $raceId = $input["race_id"];
        $horseId = $input["horse_id"];
        $comment = is_null($input["comment"]) ? "" : $input["comment"];

        $userData = [];
        if (Storage::disk('public')->exists("user_data/" . $loginUserNo . ".json")) {
            $userData = json_decode(Storage::disk('public')->get("user_data/" . $loginUserNo . ".json"), true);
        }
        $userData[$raceId][$horseId]["comment"] = $comment;

        // 書き込み
        $contents = json_encode($userData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        Storage::disk('public')->put("user_data/" . $loginUserNo . ".json", $contents);
    }

}

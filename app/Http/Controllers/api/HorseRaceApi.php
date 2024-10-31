<?php
namespace App\Http\Controllers\api;
use App\Util\NetkeibaUtil;
use App\Util\MobileLoginUtil;
use App\Util\HorseraceScoringUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


/**
 * 競馬情報API
 */
class HorseRaceApi
{
    /**
     * ログインチェック
     */
    function loginCheck(Request $request)
    {
        $statusCode = 401;
        $response = null;
        if(MobileLoginUtil::isLogin($request->cookie("chestnut-token"))){
            $response = MobileLoginUtil::getLoginUserInfo($request->cookie("chestnut-token"));
            $statusCode = 200;
        }
        return response($response, $statusCode);
    }

    /**
     * ログイン処理
     */
    function login(Request $request)
    {
        $input = $request->all();
        $response["token"] = MobileLoginUtil::login($input["user_id"], $input["password"]);
        $response["admin"] = MobileLoginUtil::getLoginUserInfo($response["token"])["admin"];
        return response($response, 200);
    }


    /**
     * スケジュールの取得
     */
    function getSchdule(Request $request)
    {
        $input = $request->all();
        $scheList = NetkeibaUtil::getScheduleList($input["sel_date"]);

        $rtnArr[] = ["id" => "", "name" => ""];
        if(count($scheList) > 0){
            foreach($scheList as $item){
                for($i=1; $i<=$item["num"]; $i++){
                    $raceId = $item["id"] . sprintf('%02d', $i);
                    if (NetkeibaUtil::existsRaceData($raceId)) {
                        $raceObj = NetkeibaUtil::GetRaceData($raceId);
                        $babaCode = substr($item["id"], 4, 2);
                        $name = config("const.BAMEI_NAME")[$babaCode] . " " . $i . "R";
                        $name .= " " . $raceObj->startingTime;
                        $name .= " " . $raceObj->raceName;
                        $rtnArr[] = ["id" => $raceId, "name" => $name];
                    }
                }
            }
        }
        return response($rtnArr, 200);
    }

    /**
     * レース情報の取得
     */
    function getRaceData(Request $request)
    {
        // レースIDを作成
        $input = $request->all();
        $raceId = $input["race_id"];

        // 取得
        $statusCode = 204;
        $rtnData = [];
        if (NetkeibaUtil::existsRaceData($raceId)) {
            $raceObj = NetkeibaUtil::GetRaceData($raceId);

            // 履歴を10件に削除
            foreach($raceObj->horseArray as $horse){
                if(count($horse->recodeArray) > 0){
                    $horse->recodeArray = array_splice($horse->recodeArray, 0, 10);
                }
            }
            // ユーザのメモ情報を取得
            $loginUserNo = MobileLoginUtil::getLoginUserInfo($request->cookie("chestnut-token"))["user_no"];
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

            $rtnData = json_encode($raceObj, JSON_UNESCAPED_UNICODE);
            $statusCode = 200;
        }

        // jsonで返却
        return response($rtnData, $statusCode);
    }


    /**
     * 日付から開催馬場を取得
     */
    function getKaisaiBaba(Request $request)
    {
        $input = $request->all();
        $scheList = NetkeibaUtil::getScheduleList($input["sel_date"]);

        $rtnObj = [];
        if(count($scheList) > 0){
            foreach($scheList as $val){
                $rtnObj[] = ["key" => $val["id"], "value" => $val["name"]];
            }
        }
        return response($rtnObj, 200);
    }

    /**
     * 開催馬場情報をスクレイピング
     */
    function scrapingKaisaiBaba(Request $request)
    {
        $input = $request->all();
        $selDate = date("Y-m-d", strtotime($input["sel_date"]));
        NetkeibaUtil::DownloadScheduleData($selDate);

        $scheList = NetkeibaUtil::getScheduleList($selDate);

        $rtnObj = [];
        if(count($scheList) > 0){
            foreach($scheList as $val){
                $rtnObj[] = ["key" => $val["id"], "value" => $val["name"]];
            }
        }
        return response($rtnObj, 200);
    }

    /**
     * 日付と開催馬場からレース取得情報を取得
     */
    function getRaceList(Request $request)
    {
        $input = $request->all();

        $selRaceId = $input["race_id"];
        $scheList = NetkeibaUtil::getScheduleList($input["sel_date"]);

        // レースリストを取得
        $rtnObj = [];
        if($selRaceId != "" && count($scheList) > 0){
            foreach($scheList as $val){
                if($val["id"] == $selRaceId){
                    for($i=1; $i<=$val["num"]; $i++) {
                        $raceId = $val["id"] . substr("00" . $i, -2);
                        $raceData = [
                            "id" => $raceId,
                            "name" => "",
                            "status" => 0
                        ];
                        // 取得済であるか？
                        if (NetkeibaUtil::existsRaceData($raceId)) {
                            $raceObj = NetkeibaUtil::getRaceData($raceId);
                            $raceData["name"] = $raceObj->raceName;
                            $raceData["status"] = 1;
                        }
                        $rtnObj[] = $raceData;
                    }
                }
            }
        }

        return response($rtnObj, 200);
    }

    /**
     * スクレイピング処理をキック
     */
    function scrapingRaceData(Request $request)
    {
        $input = $request->all();
        $raceId = $input["race_id"];

        $babaCode = substr($raceId, 4, 2);
        if(in_array($babaCode, config("const.CENTRAL_BAMEI_CODE"))){
            NetkeibaUtil::DownloadRaceInfo($raceId);
        }else{
            NetkeibaUtil::DownloadRegionRaceInfo($raceId);
        }
        // 出馬情報
        $response = ["id" => $raceId, "name" => "", "status" => 0];
        if(NetkeibaUtil::existsRaceData($raceId)){
            $raceObj = NetkeibaUtil::getRaceData($raceId);
            $response["name"] = $raceObj->raceName;
            $response["status"] = 1;
        }

        return response($response, 200);
    }


    /**
     * スコア計算API
     */
    function scoring(Request $request) {
        $input = $request->all();
        $rtnArr = HorseraceScoringUtil::scoring($input["race_id"]);
        return response($rtnArr, 200);
    }

}

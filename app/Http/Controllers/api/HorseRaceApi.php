<?php
namespace App\Http\Controllers\api;
use App\Util\NetkeibaUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use DateTime;
use Ramsey\Uuid\Uuid;


/**
 * 競馬情報API
 */
class HorseRaceApi
{
    /**
     * スケジュールの取得
     */
    function getSchdule()
    {
        $rtnArr[] = ["id" => "", "name" => ""];
        if (Storage::disk("public")->exists("schedule.json")) {
            $json = json_decode(Storage::disk("public")->get("schedule.json"));
            foreach($json as $day => $arr){
                $daytime = strtotime($day);
                if($daytime < strtotime("today") || $daytime > strtotime("+6 day")){
                    continue;
                }
                foreach($arr as $item){
                    for($i=1; $i<=$item->num; $i++){
                        $raceId = $item->id . sprintf('%02d', $i);
                        if (NetkeibaUtil::existsRaceData($raceId)) {
                            $babaCode = substr($item->id, 4, 2);
                    
                            $name = date("m/d", $daytime);
                            $name .= '(' . config('const.WEEK_SHORT_NAME')[date("w", $daytime)] . ') ';
                            $name .= config("const.BAMEI_NAME")[$babaCode];
                            $name .= ' ' . $i . 'レース';
                            $rtnArr[] = ["id" => $raceId, "name" => $name];
                        }
                    }
                }
            }
        }

        // jsonで返却
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
            $raceObj = NetkeibaUtil::GetViewRaceData($raceId);
            $rtnData = json_encode($raceObj, JSON_UNESCAPED_UNICODE);

            foreach($raceObj->horseArray as $horse){
                if(count($horse->recodeArray) > 0){
                    $horse->recodeArray = array_splice($horse->recodeArray, 0, 10);
                }
            }
            $statusCode = 200;
        }

        // jsonで返却
        return response($rtnData, $statusCode);
    }


    /**
     * ログインチェック
     */
    function loginCheck(Request $request)
    {
        $statusCode = 401;
        $token = $request->cookie("chestnut-token");
        if(!empty($token)){
            // トークンチェック
            if (Storage::disk("public")->exists("token.json")) {
                $json = json_decode(Storage::disk("public")->get("token.json"), true);
                if($token == $json["token"]){
                    $dtNow = new DateTime();
                    $dtLimit = new DateTime($json["limit"]);
                    if($dtLimit >= $dtNow){
                        $statusCode = 200;
                    }
                }
            }
        }

        return response(null, $statusCode);
    }

    /**
     * ログイン処理
     */
    function login(Request $request)
    {
        $input = $request->all();
        $userId = hash('sha256', $input["user_id"]);
        $passWd = hash('sha256', $input["password"]);

        $response = ["token" => "", "limit" => ""];

        $users = json_decode(Storage::disk("public")->get("users.json"), true);
        foreach($users as $val){
            if($userId == $val["user_id"] && $passWd == $val["password"] && $val["admin"]){
                $date = new DateTime('+2 hour');
                $response["token"] = (string) Uuid::uuid4();
                $response["limit"] = $date->format("Y-m-d H:i:s");
    
                $contents = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                Storage::disk('public')->put("token.json",  $contents);
                break;
            }
        }
        
        return response($response, 200);
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
                $rtnObj[] = ["key" => $val->id, "value" => $val->name];
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
                if($val->id == $selRaceId){
                    for($i=1; $i<=$val->num; $i++) {
                        $raceId = $val->id . substr("00" . $i, -2);
                        $raceData = [
                            "id" => $raceId,
                            "name" => "",
                            "status" => 0
                        ];
                        // 取得済であるか？
                        if (NetkeibaUtil::existsRaceData($raceId)) {
                            $raceObj = NetkeibaUtil::getRaceData($raceId);
                            $raceData["name"] = $raceObj->name;
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
            foreach($raceObj->shutsubaArray as $item){
                NetkeibaUtil::DownloadHorseInfo($item->horseId);
            }
            $response["name"] = $raceObj->name;
            $response["status"] = 1;
        }

        return response($response, 200);
    }

    /**
     * スケジュール設定用データ取得
     */
    function getSettingScheduleData(Request $request){
        $input = $request->all();
        $selDate = isset($input["sel_date"]) ? $input["sel_date"] : date('Y-m-d');

        $response = [];
        foreach(config("const.CENTRAL_BAMEI_CODE") as $code){
            $response["central"][] = array("key" => $code, "value" => config("const.BAMEI_NAME")[$code]);
        }
        foreach(config("const.REGION_BAMEI_CODE") as $code){
            $response["region"][] = array("key" => $code, "value" => config("const.BAMEI_NAME")[$code]);
        }

        $response["schedule"] = [];
        if (Storage::disk("public")->exists("schedule.json")) {
            $json = json_decode(Storage::disk("public")->get("schedule.json"), true);
            if(array_key_exists($selDate, $json)){
                $response["schedule"] = $json[$selDate];
            }
        }

        return response($response, 200);
    }

    /**
     * スケジュールの更新
     */
    function updateScheduleData(Request $request){
        $input = $request->all();
        $selDate = date("Y-m-d", strtotime($input["sel_date"]));
        $scheJson = json_decode($input["schedule"], true);

        $json = json_decode(Storage::disk("public")->get("schedule.json"), true);
        $json[$selDate] = $scheJson;
        ksort($json);

        $contents = json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        Storage::disk('public')->put("schedule.json",  $contents);

        return $this->getSettingScheduleData($request);
    }

}

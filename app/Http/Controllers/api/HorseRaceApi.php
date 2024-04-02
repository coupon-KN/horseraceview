<?php
namespace App\Http\Controllers\api;
use App\Util\NetkeibaUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


/**
 * 競馬情報API
 */
class HorseRaceApi
{

    /**
     * スケジュールの取得
     * curl -X POST "http://127.0.0.1:8000/api/horserace/schdule"
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
                            $name = date("m/d", $daytime);
                            $name .= '(' . config('const.WEEK_SHORT_NAME')[date("w", $daytime)] . ') ';
                            $name .= $this->getKaisaiName($item->id);
                            $name .= ' ' . $i . 'レース';
                            $rtnArr[] = ["id" => $raceId, "name" => $name];
                        }
                    }
                }
            }
        }

        // jsonで返却
        return $rtnArr;
    }

    /**
     * レース情報の取得
     * curl -X POST "http://127.0.0.1:8000/api/horserace/racedata" -d "race_id=2023050303"
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
            $statusCode = 200;
        }

        // jsonで返却
        return response($rtnData, $statusCode);
    }


    /**
     * IDから開催場を取得
     */
    private function getKaisaiName($raceId){
        $babaCode = substr($raceId, 4, 2);
        return config("const.BAMEI_NAME")[$babaCode];
    }
}

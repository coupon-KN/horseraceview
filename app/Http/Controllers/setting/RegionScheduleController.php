<?php
namespace App\Http\Controllers\setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use phpQuery;


class RegionScheduleController
{
    /**
     * 初期表示
     */
    function index()
    {
        $json = json_decode(Storage::disk("public")->get("regionSchedule.json"), true);
        $staDate = array_keys($json)[0];
        $staDate = date("Y-m-d", strtotime($staDate . " +1 day"));
        $endDate = date("Y-m-d", strtotime("-1 day"));

        $viewData["sta_dt"] = $staDate;
        $viewData["end_dt"] = $endDate;

        return view("setting.region-schedule", $viewData);
    }

    /**
     * レース情報取得
     */
    function getSchedule(Request $request)
    {
        $input = $request->all();
        $LIST_URL = "https://nar.netkeiba.com/top/race_list_sub.html?kaisai_date=";

        // 日付ループ
        $staDate = $input["sta_date"];
        $endDate = $input["end_date"];
        $currentDate = $staDate;

        $scheduleArray = [];
        while($currentDate <= $endDate)
        {
            $url = $LIST_URL . date('Ymd', strtotime($currentDate));
            $html = file_get_contents($url);
    

            $doc = phpQuery::newDocument($html);
            
            $cnt = count($doc->find("p.RaceList_DataTitle"));
            if($cnt > 0){
                for($i=0; $i<$cnt; $i++) {
                    $element = $doc->find("p.RaceList_DataTitle:eq(" . $i . ")");
                    $text = trim($element->text());

                    $babaName = "";
                    $babaCode = "";
                    foreach(config("const.BAMEI_NAME") as $key => $val){
                        if(strpos($text, $val) !== false){
                            $babaCode = $key;
                            $babaName = $val;
                            break;
                        }
                    }

                    $scheduleArray[$currentDate][$babaCode] = [
                        "kai" => substr("00" . preg_replace("/[^0-9]/", "", explode($babaName, $text)[0]), -2),
                        "hi" => substr("00" . preg_replace("/[^0-9]/", "", explode($babaName, $text)[1]), -2),
                    ];
                }
            }
            
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }

        $json = json_decode(Storage::disk("public")->get("regionSchedule.json"), true);
        $json = array_merge($json, $scheduleArray);
        krsort($json);
        $contents = json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        Storage::disk('public')->put("regionSchedule.json",  $contents);

        return redirect()->route('setting.regionschedule'); 
    }


}

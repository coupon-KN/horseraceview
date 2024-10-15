<?php
namespace App\Http\Controllers;
use App\Util\NetkeibaUtil;
use Illuminate\Http\Request;
use phpQuery;


/**
 * レースカレンダー
 */
class RaceCalendarController extends Controller
{
    /**
     * 初期表示
     */
    function index(Request $request)
    {
        $viewData = [];

        $queryParam = $request->query();
        if(isset($queryParam["selYm"])){
            $dtTarget = date("Y-m-d", strtotime($queryParam["selYm"] . "01"));
        }else{
            $dtTarget = date("Y-m-d");
        }

        // カレンダー情報を作成
        $viewData["targetDate"] = $dtTarget;
        $viewData["prevMonth"] = date('Ym', strtotime(substr($dtTarget, 0, 8) . '01 -1 Month'));
        $viewData["nextMonth"] = date('Ym', strtotime(substr($dtTarget, 0, 8) . '01 +1 Month'));
        $viewData["calendar"] = $this->createCalendarArray($dtTarget);

        // 開催情報を取得
        $kaisaiArray = [];

        $json = NetkeibaUtil::getScheduleList("all");
        foreach($viewData["calendar"] as $week){
            foreach($week as $day){
                if(array_key_exists($day, $json)){
                    $kaisaiArray[$day]["central"] = [];
                    $kaisaiArray[$day]["region"] = [];
                    foreach($json[$day] as $item){
                        $babaCode = substr($item["id"], 4, 2);
                        $area = in_array($babaCode, config("const.CENTRAL_BAMEI_CODE")) ? "central" : "region";
                        $kaisaiArray[$day][$area][] = array(
                            "id" => $item["id"],
                            "name" => config("const.BAMEI_NAME")[$babaCode],
                            "num" => $item["num"]
                        );
                    }
                }
            }
        }
        $viewData["kaisaiData"] = $kaisaiArray;

        return view("race-calendar", $viewData);
    }

    /**
     * レース情報を取得
     */
    function getRaceInfo(Request $request)
    {
        $input = $request->all();
        $selDate = $input["sel_date"];
        $raceId = $input["race_id"];

        $raceNum = 0;

        $scheArr = NetkeibaUtil::getScheduleList($selDate);
        if(count($scheArr) > 0) {
            foreach($scheArr as $row){
                if($row["id"] == $raceId){
                    $raceNum = $row["num"];
                    break;
                }
            }
        }

        $babaCode = substr($raceId, 4, 2);
        $rtnData["title"] = date("n月j日 ", strtotime($selDate)) . config("const.BAMEI_NAME")[$babaCode];

        for($i=1; $i<=$raceNum; $i++){
            $searchId = $raceId . sprintf('%02d', $i);
            if (NetkeibaUtil::existsRaceData($searchId)) {
                $raceData = NetkeibaUtil::getRaceData($searchId);
                unset($raceData->shutsubaArray);
                $rtnData["data"][] = array(
                    "isExists" => true,
                    "raceId" => $raceData->raceId,
                    "name" => $raceData->raceName,
                    "startingTime" => $raceData->startingTime,
                    "distance" => config("const.GROUND_SHORT_NAME")[$raceData->groundType] . $raceData->distance . "m",
                    "horseCount" => $raceData->horseCount . "頭",
                );
            }else{
                $rtnData["data"][] = array(
                    "isExists" => false,
                    "raceId" => $searchId
                );
            }
        }

        $view = view('race-calendar-detail', $rtnData)->render();
        return response(["view" => $view], 200);
    }

    /**
     * スケジュールのデータをスクレイピング
     */
    function getScheduleData(Request $request)
    {
        $input = $request->all();
        $selDate = date("Y-m-d", strtotime($input["sel_date"]));

        NetkeibaUtil::DownloadScheduleData($selDate);

        return redirect()->route("calendar.index", ["selYm" => date("Ym", strtotime($selDate))]);
    }

    /**
     * カレンダー配列の作成
     */
    private function createCalendarArray($dtTarget)
    {
        $staDate = date("Y-m-01", strtotime($dtTarget));
        $endDate = date("Y-m-t", strtotime($staDate));
        $currentDate = $staDate;

        $calendarArray[] = ["","","","","","",""];
        $row = 0;
        while($currentDate <= $endDate){
            $week = intval(date('w', strtotime($currentDate)));
            $calendarArray[$row][$week] = $currentDate;
            if($week == 6 && $currentDate != $endDate){
                $row ++;
                $calendarArray[] = ["","","","","","",""];
            }
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }
        return $calendarArray;
    }

    /**
     * 中央のスケジュール情報取得
     */
    function scrapingScheduleData($strYmd, $areaFlg)
    {
        $scheArr = [];
        try {
            if($areaFlg == "c"){
                $link = "https://race.netkeiba.com/top/race_list_sub.html?kaisai_date=";
            }else{
                $link = "https://nar.netkeiba.com/top/race_list_sub.html?kaisai_date=";
            }
            $html = file_get_contents($link . date("Ymd", strtotime($strYmd)));

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

                    $sche = ["id" => "", "name" => "", "num" => 12];
                    if($areaFlg == "c"){
                        $kai = preg_replace("/[^0-9]/", "", explode($babaName, $text)[0]);
                        $hi = preg_replace("/[^0-9]/", "", explode($babaName, $text)[1]);
                        $sche["id"] = substr($strYmd, 0, 4) . $babaCode . substr("00" . $kai, -2) . substr("00" . $hi, -2);
                        $sche["name"] = sprintf("%d回 %s %d日目", $kai, $babaName, $hi);
                    }else{
                        $sche["id"] = substr($strYmd, 0, 4) . $babaCode . date("md", strtotime($strYmd));
                        $sche["name"] = $babaName . " " . date("n月j日", strtotime($strYmd)) . "(" . config("const.WEEK_SHORT_NAME")[date("w", strtotime($strYmd))] . ")";
                        $sche["num"] = count($doc->find(".RaceList_Data:eq(" . $i . ") .RaceList_DataItem")->elements);
                    }
                    $scheArr[] = $sche;
                }
            }
        }
        catch(\Exception $err){
            $scheArr = [];
        }

        return $scheArr;
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
        if(NetkeibaUtil::existsRaceData($raceId)){
            $raceObj = NetkeibaUtil::getRaceData($raceId);
        }

        // 返却
        unset($raceObj->shutsubaArray);
        $rtnData["data"] = array(
            "raceNo" => intval(substr($raceId, -2)),
            "raceId" => $raceObj->raceId,
            "startingTime" => $raceObj->startingTime,
            "distance" => config("const.GROUND_SHORT_NAME")[$raceObj->groundType] . $raceObj->distance . "m",
            "horseCount" => $raceObj->horseCount . "頭",
            "name" => $raceObj->raceName,
            "detailUrl" => route('detail.index', $raceObj->raceId)
        );

        return response($rtnData, 200);
    }

}

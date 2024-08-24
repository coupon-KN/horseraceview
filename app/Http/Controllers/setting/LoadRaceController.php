<?php
namespace App\Http\Controllers\setting;
use App\Util\NetkeibaUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


/**
 * データ取得をキックする画面
 */
class LoadRaceController
{
    private $viewData = [
        "sel_date" => "",
        "sel_sche" => "",
        "schedule_list" => [],
        "race_list" => []
    ];

    /**
     * 初期表示
     */
    function index(Request $request)
    {
        // 日付
        $selDate = isset($request["sel_date"]) ? $request["sel_date"] : date('Y-m-d');
        // レースIDを取得
        $selRaceId = isset($request["sel_sche"]) ? $request["sel_sche"] : "";
        // 日付からスケジュールを取得
        $scheList = NetkeibaUtil::getScheduleList($selDate);

        // レースリストを取得
        if($selRaceId != "" && count($scheList) > 0){
            foreach($scheList as $val){
                if($val->id == $selRaceId){
                    for($i=1; $i<=$val->num; $i++) {
                        $raceId = $val->id . substr("00" . $i, -2);
                        $raceData = [
                            "id" => $raceId,
                            "name" => "",
                            "put_flg" => false
                        ];
                        // 取得済であるか？
                        if (NetkeibaUtil::existsRaceData($raceId)) {
                            $raceObj = NetkeibaUtil::getRaceData($raceId);
                            $raceData["name"] = $raceObj->name;
                            $raceData["put_flg"] = true;
                        }
                        $this->viewData["race_list"][] = $raceData;
                    }
                }
            }
        }

        $this->viewData["sel_date"] = $selDate;
        $this->viewData["sel_sche"] = $selRaceId;
        $this->viewData["schedule_list"] = $scheList;
        return view("setting.load-race", $this->viewData);
    }

    /**
     * レース情報取得
     */
    function getRaceData(Request $request)
    {
        $input = $request->all();
        $rtnParam = ["sel_date" => $input["sel_date"], "sel_sche" => $input["sel_sche"]];
        $raceId = $input["race_id"];

        $this->callNetkeibaScrapingMethod($raceId);

        return redirect()->route('setting.raceload', $rtnParam); 
    }

    /**
     * レース情報の一括取得
     */
    function getBulkRaceData(Request $request)
    {
        $input = $request->all();
        $rtnParam = ["sel_date" => $input["sel_date"], "sel_sche" => $input["sel_sche"]];
        $raceIdArr = $input["race_id"];

        foreach($raceIdArr as $raceId){
            $this->callNetkeibaScrapingMethod($raceId);
        }

        return redirect()->route('setting.raceload', $rtnParam); 
    }

    /**
     * スクレイピングメソッドの呼び出し
     */
    private function callNetkeibaScrapingMethod($raceId) {
        $babaCode = substr($raceId, 4, 2);
        if(in_array($babaCode, config("const.CENTRAL_BAMEI_CODE"))){
            // 中央競馬
            NetkeibaUtil::DownloadRaceInfo($raceId);
            if(NetkeibaUtil::existsRaceData($raceId)){
                $raceObj = NetkeibaUtil::getRaceData($raceId);
                foreach($raceObj->shutsubaArray as $item){
                    NetkeibaUtil::DownloadHorseInfo($item->horseId);
                }
            }
        }else{
            // 地方競馬
            NetkeibaUtil::DownloadRegionRaceInfo($raceId);
            if(NetkeibaUtil::existsRaceData($raceId)){
                $raceObj = NetkeibaUtil::getRaceData($raceId);
                foreach($raceObj->shutsubaArray as $item){
                    NetkeibaUtil::DownloadHorseInfo($item->horseId);
                }
            }
        }
    }

}

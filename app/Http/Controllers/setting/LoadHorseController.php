<?php
namespace App\Http\Controllers\setting;
use App\Util\NetkeibaUtil;
use Illuminate\Http\Request;


/**
 * 競走馬データ取得をキックする画面
 */
class LoadHorseController
{
    private $viewData = [
        "race_obj" => null,
        "put_arr" => []
    ];

    /**
     * 初期表示
     */
    function index($raceId)
    {
        // レースIDを取得
        $raceObj = null;
        if (NetkeibaUtil::existsRaceData($raceId)) {
            $raceObj = NetkeibaUtil::getRaceData($raceId);
            foreach($raceObj->shutsubaArray as $item){
                $this->viewData["put_arr"][] = NetkeibaUtil::existsHorseData($item->horseId);
            }
        }

        $this->viewData["race_obj"] = $raceObj;
        return view("setting.load-horse", $this->viewData);
    }

    /**
     * 競走馬情報取得
     */
    function getHorseData(Request $request)
    {
        $input = $request->all();
        $rtnParam = ["race_id" => $input["race_id"]];
        $horseId = $input["horse_id"];

        // 取得してファイルに書き込み
        NetkeibaUtil::DownloadHorseInfo($horseId);

        return redirect()->route('setting.horseload', $rtnParam); 
    }


}

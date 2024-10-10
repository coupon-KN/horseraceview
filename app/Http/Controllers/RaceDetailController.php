<?php
namespace App\Http\Controllers;
use App\Util\NetkeibaUtil;
use App\Util\HorseraceScoringUtil;
use Illuminate\Http\Request;


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
            $raceObj = NetkeibaUtil::GetViewRaceData($raceId);

            $babaCode = substr($raceId, 4, 2);
            $raceNo = intval(substr($raceId, -2));
            $viewData["pageTitle"] = config("const.BAMEI_NAME")[$babaCode] . $raceNo . "R " . $raceObj->raceName;
            $viewData["menuTitle"] = config("const.BAMEI_NAME")[$babaCode] . $raceNo . "R " . $raceObj->raceName;
            $viewData["centralFlg"] = (in_array($babaCode, config("const.CENTRAL_BAMEI_CODE")));
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

}

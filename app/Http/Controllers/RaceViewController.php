<?php
namespace App\Http\Controllers;
use App\Util\NetkeibaUtil;
use App\Models\ViewRaceData;
use Illuminate\Http\Request;


/**
 * レースビューワー
 */
class RaceViewController extends Controller
{
    /**
     * 初期表示
     */
    function index($raceId)
    {
        // レースIDを取得
        if (NetkeibaUtil::existsRaceData($raceId)) {
            $raceObj = NetkeibaUtil::GetViewRaceData($raceId);
        }else{
            $raceObj = null;
        }

        $viewData["race"] = $raceObj;

        return view("raceview", $viewData);
    }

}

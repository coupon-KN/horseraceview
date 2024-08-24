<?php
namespace App\Http\Controllers\api;
use App\Util\NetkeibaUtil;
use App\Models\ViewRaceData;
use App\Models\ViewHorseData;


/**
 * レース採点
 */
class RaceScoringApi
{
    const POTENTIAL_ARRAY = ["G1", "G2", "G3", "リステッド", "オープン特別", "３勝クラス", "２勝クラス", "１勝クラス", "未勝利"];
    const BABA_RANK1 = ['01', '05', '06', '08', '09'];
    const BABA_RANK2 = ['02', '04', '07'];
    const BABA_RANK3 = ['03', '10'];
    const DISTANCE_RANK1 = [2000];
    const DISTANCE_RANK2 = [1600, 2400];
    const DISTANCE_RANK3 = [1200, 2500];


    /**
     * 採点処理
     */
    function scoring($raceId)
    {
        $returnArr = [];
        if (!NetkeibaUtil::existsRaceData($raceId)) {
            return response([], 200);
        }
        $raceData = NetkeibaUtil::GetViewRaceData($raceId);

        foreach($raceData->horseArray as $horse) {

            // クラス判定
            $class = $this->checkClass($horse);
            // 勝ち馬場等級
            $winBaba = $this->winBabaRank($horse);
            // 勝ち距離等級
            $distance = $this->checkDistance($raceData, $horse);


            $returnArr[] = array(
                "umaban" => $horse->umaban,
                "name" => $horse->name,
                "class" => $class,
                "babaRank" => $winBaba,
                "distance" => $distance
            );
        }



        return response($returnArr, 200);
    }


    /**
     * クラスチェック
     */
    private function checkClass(ViewHorseData $horse) {
        $potential = count($this::POTENTIAL_ARRAY) - 1;
        foreach($horse->recodeArray as $recode) {
            // 着差0.2以下
            if($recode->difference <= 0.2){
                if(strpos($recode->raceName, "(GI)") !== false){
                    $potential = min(0, $potential);
                }elseif(strpos($recode->raceName, "(GII)") !== false){
                    $potential = min(1, $potential);
                }elseif(strpos($recode->raceName, "(GIII)") !== false){
                    $potential = min(2, $potential);
                }elseif(strpos($recode->raceName, "(L)") !== false){
                    $potential = min(3, $potential);
                }elseif(strpos($recode->raceName, "(OP)") !== false){
                    $potential = min(4, $potential);
                }elseif(strpos($recode->raceName, "3勝クラス") !== false){
                    $potential = min(5, $potential);
                }elseif(strpos($recode->raceName, "2勝クラス") !== false){
                    $potential = min(6, $potential);
                }elseif(strpos($recode->raceName, "1勝クラス") !== false){
                    $potential = min(7, $potential);
                }
            }
        }
        return $this::POTENTIAL_ARRAY[$potential];
    }

    /**
     * 勝ち馬場ランク
     */
    private function winBabaRank(ViewHorseData $horse) {
        $rankNum = array("rank1" => 0, "rank2" => 0, "rank3" => 0);
        //$recodeArray = array_splice($horse->recodeArray, 0, 10);
        foreach($horse->recodeArray as $recode) {
            if($recode->difference <= 0.2){
                $babaCode = "";
                foreach(config("const.BAMEI_NAME") as $key => $val){
                    if(strpos($recode->baba, $val) !== false){
                        $babaCode = $key;
                        break;
                    }
                }
                if(in_array($babaCode, $this::BABA_RANK1)){
                    $rankNum["rank1"] += 1;
                }elseif(in_array($babaCode, $this::BABA_RANK2)){
                    $rankNum["rank2"] += 1;
                }elseif(in_array($babaCode, $this::BABA_RANK3)){
                    $rankNum["rank3"] += 1;
                }
            }
        }
        return $rankNum;
    }

    /**
     * 勝ち距離ランク
     */
    private function checkDistance(ViewRaceData $raceData, ViewHorseData $horse) {
        $raceRank = 4;
        if(in_array($raceData->distance, $this::DISTANCE_RANK1)){
            $raceRank = 1;
        }elseif(in_array($raceData->distance, $this::DISTANCE_RANK2)){
            $raceRank = 2;
        }elseif(in_array($raceData->distance, $this::DISTANCE_RANK3)){
            $raceRank = 3;
        }

        $point = 0;
        foreach($horse->recodeArray as $recode) {
            if($raceData->groundType == $recode->groundType) {
                $historyRank = 4;
                if(in_array($recode->distance, $this::DISTANCE_RANK1)){
                    $historyRank = 1;
                }elseif(in_array($recode->distance, $this::DISTANCE_RANK2)){
                    $historyRank = 2;
                }elseif(in_array($recode->distance, $this::DISTANCE_RANK3)){
                    $historyRank = 3;
                }

                // 同ランク、３馬身以下
                if($raceRank < 4 && $historyRank < 4){
                    if($raceRank == $historyRank && $recode->difference <= 0.5) {
                        $point += 1;
                    }
                }else{
                    if($raceData->distance == $recode->distance && $recode->difference <= 0.5) {
                        $point += 1;
                    }
                }
            }
        }

        $rtnData = "不明";
        if(count($horse->recodeArray) > 0){
            $percent = floor($point / count($horse->recodeArray) * 100);
            $rtnData = $percent . "%";
        }

        return $rtnData;
    }

}

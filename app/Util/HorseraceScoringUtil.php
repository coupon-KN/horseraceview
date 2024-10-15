<?php
namespace App\Util;
use App\Models\RaceData;
use App\Models\HorseData;


/**
 * 競走馬採点クラス
 */
class HorseraceScoringUtil
{
    private static $BABA_RANK1 = ['01', '05', '06', '08', '09'];
    private static $BABA_RANK2 = ['02', '04', '07'];
    private static $BABA_RANK3 = ['03', '10'];
    private static $DISTANCE_RANK1 = [2000];
    private static $DISTANCE_RANK2 = [1600, 2400];
    private static $DISTANCE_RANK3 = [1200, 2500];


    /**
     * 採点処理
     */
    public static function scoring($raceId)
    {
        $returnArr = [];
        if (!NetkeibaUtil::existsRaceData($raceId)) {
            return response([], 200);
        }
        $raceData = NetkeibaUtil::GetRaceData($raceId);

        // 対象のレースランク
        $raceGarade = config("const.RACE_CLASS_RANK")[array_key_last(config("const.RACE_CLASS_RANK"))];
        if(array_key_exists($raceData->raceGarade, config("const.RACE_CLASS_RANK"))){
            $raceGarade = config("const.RACE_CLASS_RANK")[$raceData->raceGarade];
        }
                    
        foreach($raceData->horseArray as $horse) {

            // 勝利した中で一番高いクラス等級
            $highestRank = HorseraceScoringUtil::searchHighestRank($horse);
            $highestRankScore = 0;
            if($highestRank < $raceGarade) {
                $highestRankScore = 100;
            }elseif($highestRank == $raceGarade) {
                $highestRankScore = 90;
            }else{
                $highestRankScore = 90 - ($highestRank - $raceGarade) * 10;
            }

            // 1年以内に勝利したクラス等級
            $highestRankYear = HorseraceScoringUtil::searchHighestRank($horse, date("Y-m-d", strtotime("-1 year")));
            $highestRankYearScore = 0;
            if($highestRankYear < $raceGarade) {
                $highestRankYearScore = 100;
            }elseif($highestRankYear == $raceGarade) {
                $highestRankYearScore = 90;
            }else{
                $highestRankYearScore = 90 - ($highestRankYear - $raceGarade) * 10;
            }

            // 1年以内で勝った競馬場の等級
            $raceTrackScore = HorseraceScoringUtil::calcRaceTrackScore($horse);

            // 勝ち距離等級
            $distanceScore = HorseraceScoringUtil::calcDistanceScore($raceData, $horse);

            // 点数
            $score = ceil($highestRankScore * 0.15 + $highestRankYearScore * 0.35 + $raceTrackScore * 0.2 + $distanceScore * 0.3);

            $returnArr[] = array(
                "umaban" => $horse->umaban,
                "name" => $horse->name,
                "score" => $score,
            );
        }

        return $returnArr;
    }


    /**
     * 勝利した中で一番高いクラス等級
     */
    private static function searchHighestRank(HorseData $horse, $limitYmd = "") {
        $classRankArray = config("const.RACE_CLASS_RANK");
        $highestRank = $classRankArray[array_key_last($classRankArray)];

        foreach($horse->recodeArray as $recode) {
            // 期限が過ぎているか
            if($limitYmd != "" && strtotime($recode->date) < strtotime($limitYmd)) {
                break;
            }
            // 着差0.2以下
            if($recode->difference <> "" && $recode->difference <= 0.2){
                if(strpos($recode->raceName, "(GI)") !== false){
                    $highestRank = min($classRankArray["G1"], $highestRank);
                }elseif(strpos($recode->raceName, "(GII)") !== false){
                    $highestRank = min($classRankArray["G2"], $highestRank);
                }elseif(strpos($recode->raceName, "(GIII)") !== false){
                    $highestRank = min($classRankArray["G3"], $highestRank);
                }elseif(strpos($recode->raceName, "(L)") !== false){
                    $highestRank = min($classRankArray["リステッド"], $highestRank);
                }elseif(strpos($recode->raceName, "(OP)") !== false){
                    $highestRank = min($classRankArray["オープン"], $highestRank);
                }elseif(strpos($recode->raceName, "3勝クラス") !== false){
                    $highestRank = min($classRankArray["３勝クラス"], $highestRank);
                }elseif(strpos($recode->raceName, "2勝クラス") !== false){
                    $highestRank = min($classRankArray["２勝クラス"], $highestRank);
                }elseif(strpos($recode->raceName, "1勝クラス") !== false){
                    $highestRank = min($classRankArray["１勝クラス"], $highestRank);
                }
            }
        }

        return $highestRank;
    }


    /**
     * 1年以内で勝った競馬場の等級
     */
    private static function calcRaceTrackScore(HorseData $horse) {
        $rankArray = array("num1" => 0, "win1" => 0, "num2" => 0, "win2" => 0, "num3" => 0, "win3" => 0);
        foreach($horse->recodeArray as $recode) {
            // 1年以内であること
            if(strtotime($recode->date) < strtotime("-1 year")) {
                break;
            }
            
            $babaCode = "";
            foreach(config("const.BAMEI_NAME") as $key => $val){
                if(strpos($recode->baba, $val) !== false){
                    $babaCode = $key;
                    break;
                }
            }

            if(in_array($babaCode, HorseraceScoringUtil::$BABA_RANK1)){
                $rankArray["num1"] += 1;
                $rankArray["win1"] += ($recode->difference <> "" && $recode->difference <= 0.2) ? 1 : 0;
            }elseif(in_array($babaCode, HorseraceScoringUtil::$BABA_RANK2)){
                $rankArray["num2"] += 1;
                $rankArray["win2"] += ($recode->difference <> "" && $recode->difference <= 0.2) ? 1 : 0;
            }elseif(in_array($babaCode, HorseraceScoringUtil::$BABA_RANK3)){
                $rankArray["num3"] += 1;
                $rankArray["win3"] += ($recode->difference <> "" && $recode->difference <= 0.2) ? 1 : 0;
            }
        }
        
        $rtnScore = 0.0;
        $totalNum = $rankArray["num1"] + $rankArray["num2"] + $rankArray["num3"];
        $point = ($totalNum > 0) ? 100.0 / $totalNum : 0;
        $rtnScore += $rankArray["num1"] * $point;
        $rtnScore += $rankArray["num2"] * ($point / 2);
        $rtnScore += $rankArray["num3"] * ($point / 4);
        return ceil($rtnScore);
    }


    /**
     * 1年以内で勝った距離ランク
     */
    private static function calcDistanceScore(RaceData $raceData, HorseData $horse) {
        $raceRank = 4;
        $raceRank = in_array($raceData->distance, HorseraceScoringUtil::$DISTANCE_RANK1) ? 1 : $raceRank;
        $raceRank = in_array($raceData->distance, HorseraceScoringUtil::$DISTANCE_RANK2) ? 2 : $raceRank;
        $raceRank = in_array($raceData->distance, HorseraceScoringUtil::$DISTANCE_RANK3) ? 3 : $raceRank;

        $point = 0;
        $count = 0;
        foreach($horse->recodeArray as $recode) {
            // 1年以内であること
            if(strtotime($recode->date) < strtotime("-1 year")) {
                break;
            }
            $count++;
            if($raceData->groundType == $recode->groundType) {
                $historyRank = 4;
                $historyRank = in_array($recode->distance, HorseraceScoringUtil::$DISTANCE_RANK1) ? 1 : $historyRank;
                $historyRank = in_array($recode->distance, HorseraceScoringUtil::$DISTANCE_RANK2) ? 2 : $historyRank;
                $historyRank = in_array($recode->distance, HorseraceScoringUtil::$DISTANCE_RANK3) ? 3 : $historyRank;

                // 同ランク、３馬身以下
                if($raceRank < 4 && $historyRank < 4){
                    if($recode->difference <> "" && $recode->difference <= 0.5 && $raceRank == $historyRank) {
                        $point += 1;
                    }
                }else{
                    if($recode->difference <> "" && $recode->difference <= 0.5 && $raceData->distance == $recode->distance) {
                        $point += 1;
                    }
                }
            }
        }

        return ($count > 0) ? ceil($point / $count * 100) : 0;
    }


}

<?php
namespace App\Util;
use App\Models\FileRaceData;
use App\Models\ShutsubaInfo;
use App\Models\FileHorse;
use App\Models\FileParentHorse;
use App\Models\RaceHistory;
use App\Models\ViewRaceData;
use App\Models\ViewHorseData;
use Illuminate\Support\Facades\Storage;
use phpQuery;


/**
 * netkeibaスクレイピング
 */
class NetkeibaUtil
{
    public static $SCHEDULE_FILE = "schedule.json";

    /**
     * スケジュールリストの取得
     */
    public static function getScheduleList($date){
        $rtnArr = [];
        if (Storage::disk('public')->exists(NetkeibaUtil::$SCHEDULE_FILE)) {
            $json = json_decode(Storage::disk('public')->get(NetkeibaUtil::$SCHEDULE_FILE));
            foreach($json as $key => $val){
                if($date == $key) {
                    $rtnArr = $val;
                    break;
                }
            }
        }
        return $rtnArr;
    }

    /**
     * レース情報が取得済みか確認
     */
    public static function existsRaceData($raceId): bool {
        return Storage::disk('public')->exists("race/" . $raceId . ".json");
    }

    /**
     * レース情報取得
     */
    public static function getRaceData($raceId) : FileRaceData {
        $contents = Storage::disk('public')->get("race/" . $raceId . ".json");
        $json = json_decode($contents, true);

        $race = new FileRaceData();
        $race->raceId = $json["raceId"];
        $race->name = $json["name"];
        $race->startingTime = $json["startingTime"];
        $race->groundType = $json["groundType"];
        $race->distance = $json["distance"];
        $race->direction = $json["direction"];
        $race->horseCount = $json["horseCount"];
        foreach($json["shutsubaArray"] as $row) {
            $shutsuba = new ShutsubaInfo();
            $shutsuba->horseId = $row["horseId"];
            $shutsuba->link = $row["link"];
            $shutsuba->waku = $row["waku"];
            $shutsuba->umaban = $row["umaban"];
            $shutsuba->name = $row["name"];
            $shutsuba->age = $row["age"];
            $shutsuba->jockey = $row["jockey"];
            $shutsuba->isCancel = $row["isCancel"];
            $race->shutsubaArray[] = $shutsuba;
        }
        return $race;
    }

    /**
     * 競走馬情報が取得済みか確認
     */
    public static function existsHorseData($horseId): bool {
        return Storage::disk('public')->exists("horse/" . $horseId . ".json");
    }

    /**
     * 競走馬情報の取得
     */
    public static function getHorseData($horseId) : FileHorse {
        $contents = Storage::disk('public')->get("horse/" . $horseId . ".json");
        $json = json_decode($contents, true);

        $horse = new FileHorse();
        $horse->horseId = $json["horseId"];
        $horse->name = $json["name"];
        $horse->age = $json["age"];
        $horse->raceTotal = $json["raceTotal"];
        $horse->rank1Count = $json["rank1Count"];
        $horse->rank2Count = $json["rank2Count"];
        $horse->rank3Count = $json["rank3Count"];
        $horse->rankEtcCount = $json["rankEtcCount"];
        $horse->dadName = $json["dadName"];
        $horse->dadRecode = $json["dadRecode"];
        $horse->dadWinRate = $json["dadWinRate"];
        $horse->dadPodiumRate = $json["dadPodiumRate"];
        $horse->dadSohuName = $json["dadSohuName"];
        $horse->dadSohuRecode = $json["dadSohuRecode"];
        $horse->dadSohuWinRate = $json["dadSohuWinRate"];
        $horse->dadSohuPodiumRate = $json["dadSohuPodiumRate"];
        $horse->dadSoboName = $json["dadSoboName"];
        $horse->dadSoboRecode = $json["dadSoboRecode"];
        $horse->dadSoboWinRate = $json["dadSoboWinRate"];
        $horse->dadSoboPodiumRate = $json["dadSoboPodiumRate"];
        $horse->mamName = $json["mamName"];
        $horse->mamRecode = $json["mamRecode"];
        $horse->mamWinRate = $json["mamWinRate"];
        $horse->mamPodiumRate = $json["mamPodiumRate"];
        $horse->mamSohuName = $json["mamSohuName"];
        $horse->mamSohuRecode = $json["mamSohuRecode"];
        $horse->mamSohuWinRate = $json["mamSohuWinRate"];
        $horse->mamSohuPodiumRate = $json["mamSohuPodiumRate"];
        $horse->mamSoboName = $json["mamSoboName"];
        $horse->mamSoboRecode = $json["mamSoboRecode"];
        $horse->mamSoboWinRate = $json["mamSoboWinRate"];
        $horse->mamSoboPodiumRate = $json["mamSoboPodiumRate"];
        foreach($json["recodeArray"] as $row) {
            $history = new RaceHistory();
            $history->date = $row["date"];
            $history->baba = $row["baba"];
            $history->tenki = $row["tenki"];
            $history->raceNo = $row["raceNo"];
            $history->raceName = $row["raceName"];
            $history->horseCount = $row["horseCount"];
            $history->waku = $row["waku"];
            $history->umaban = $row["umaban"];
            $history->odds = $row["odds"];
            $history->ninki = $row["ninki"];
            $history->rankNo = $row["rankNo"];
            $history->jockey = $row["jockey"];
            $history->kinryo = $row["kinryo"];
            $history->distance = $row["distance"];
            $history->groundType = $row["groundType"];
            $history->groundShortName = config("const.GROUND_SHORT_NAME")[$row["groundType"]];
            $history->condition = $row["condition"];
            $history->time = $row["time"];
            $history->difference = $row["difference"];
            $history->pointTime = $row["pointTime"];
            $history->firstPace = $row["firstPace"];
            $history->latterPace = $row["latterPace"];
            $history->agari600m = $row["agari600m"];
            $history->weight = $row["weight"];
            $history->winHorse = $row["winHorse"];
            $horse->recodeArray[] = $history;
        }
        return $horse;
    }

    /**
     * 表示用のレース情報取得
     */
    public static function GetViewRaceData($raceId) : ViewRaceData
    {
        $view = new ViewRaceData();

        // レース情報
        $raceFile = NetkeibaUtil::getRaceData($raceId);

        $view->raceId = $raceId;
        $view->kaisai = NetkeibaUtil::getKaisaiName($raceId);
        $view->raceName = $raceFile->name;
        $view->startingTime = $raceFile->startingTime;
        $view->groundType  = $raceFile->groundType;
        $view->distance  = $raceFile->distance;
        $view->direction  = $raceFile->direction;
        $view->horseCount = $raceFile->horseCount;
        // レース情報
        $view->raceInfo = $raceFile->startingTime . "発走 ";
        $view->raceInfo .= config("const.GROUND_NAME")[$raceFile->groundType];
        $view->raceInfo .= $raceFile->distance . "m";
        $view->raceInfo .= "("  . config("const.DIRECTION_NAME")[$raceFile->direction] . ") ";
        $view->raceInfo .= $raceFile->horseCount . "頭";
        // 競走馬情報
        foreach($raceFile->shutsubaArray As $item)
        {
            $horse = new ViewHorseData();
            $horse->waku = $item->waku;
            $horse->umaban = $item->umaban;
            $horse->name = $item->name;
            $horse->age = $item->age;
            $horse->jockey = $item->jockey;
            $horse->isCancel = $item->isCancel;
            // 馬情報
            if(NetkeibaUtil::existsHorseData($item->horseId)){
                $fileHorse = NetkeibaUtil::getHorseData($item->horseId);
                // 成績
                $horse->recode = sprintf("[%d-%d-%d-%d]", $fileHorse->rank1Count, $fileHorse->rank2Count, $fileHorse->rank3Count, $fileHorse->rankEtcCount);
                if($fileHorse->raceTotal > 0){
                    $horse->winRate = round($fileHorse->rank1Count / $fileHorse->raceTotal * 100);
                    $horse->podiumRate = round(($fileHorse->rank1Count + $fileHorse->rank2Count + $fileHorse->rank3Count) / $fileHorse->raceTotal * 100);
                }else{
                    $horse->winRate = 0;
                    $horse->podiumRate = 0;
                }
                // 履歴
                if(count($fileHorse->recodeArray) > 0){
                    //$horse->recodeArray = array_splice($fileHorse->recodeArray, 0, 5);
                    $horse->recodeArray = $fileHorse->recodeArray;
                }else{
                    $horse->recodeArray = [];
                }
                // 親の情報
                $horse->dadName = $fileHorse->dadName;
                $horse->dadRecode = $fileHorse->dadRecode;
                $horse->dadWinRate = $fileHorse->dadWinRate;
                $horse->dadPodiumRate = $fileHorse->dadPodiumRate;
                $horse->dadSohuName = $fileHorse->dadSohuName;
                $horse->dadSohuRecode = $fileHorse->dadSohuRecode;
                $horse->dadSohuWinRate = $fileHorse->dadSohuWinRate;
                $horse->dadSohuPodiumRate = $fileHorse->dadSohuPodiumRate;
                $horse->dadSoboName = $fileHorse->dadSoboName;
                $horse->dadSoboRecode = $fileHorse->dadSoboRecode;
                $horse->dadSoboWinRate = $fileHorse->dadSoboWinRate;
                $horse->dadSoboPodiumRate = $fileHorse->dadSoboPodiumRate;
                $horse->mamName = $fileHorse->mamName;
                $horse->mamRecode = $fileHorse->mamRecode;
                $horse->mamWinRate = $fileHorse->mamWinRate;
                $horse->mamPodiumRate = $fileHorse->mamPodiumRate;
                $horse->mamSohuName = $fileHorse->mamSohuName;
                $horse->mamSohuRecode = $fileHorse->mamSohuRecode;
                $horse->mamSohuWinRate = $fileHorse->mamSohuWinRate;
                $horse->mamSohuPodiumRate = $fileHorse->mamSohuPodiumRate;
                $horse->mamSoboName = $fileHorse->mamSoboName;
                $horse->mamSoboRecode = $fileHorse->mamSoboRecode;
                $horse->mamSoboWinRate = $fileHorse->mamSoboWinRate;
                $horse->mamSoboPodiumRate = $fileHorse->mamSoboPodiumRate;

            }else{
                $horse->recode = 0;
                $horse->winRate = 0;
                $horse->podiumRate = 0;
                $horse->recodeArray = [];
            }

            $view->horseArray[] = $horse;
        }

        return $view;
    }


    /**
     * レース情報の取得
     */
    public static function DownloadRaceInfo($raceId)
    {
        // netkeibaのサイトからhtml情報を取得
        $html = file_get_contents("https://race.netkeiba.com/race/shutuba.html?race_id=" . $raceId);
        if(!$html){
            return;
        }

        $html = mb_convert_encoding($html, "UTF-8", "EUC-JP");

        $raceFile = new FileRaceData();
        try {
            $doc = phpQuery::newDocument($html);

            // レースID
            $raceFile->raceId = $raceId;

            // レース名
            $raceFile->name = trim($doc->find("#page div.RaceColumn01 h1.RaceName")->text());

            // レース情報
            $strRace = $doc->find("#page div.RaceColumn01 div.RaceData01")->text();
            $strRace = preg_replace('/　|\s+/', '', $strRace);
            $raceArr = explode("/", $strRace);
            // 発送時刻
            $raceFile->startingTime = str_replace("発走", "", $raceArr[0]);

            // 種類
            $type = mb_substr(preg_replace('/　|\s+/', '', $raceArr[1]), 0, 1);
            if($type == "芝"){
                $raceFile->groundType = 1;
            }elseif($type == "ダ"){
                $raceFile->groundType = 2;
            }elseif($type == "障"){
                $raceFile->groundType = 3;
            }
            // 距離
            $raceFile->distance = intval(preg_replace('/[^0-9]/', '', $raceArr[1]));
            // 向き
            if(false !== strpos($raceArr[1], "左")){
                $raceFile->direction = 1;
            }elseif(false !== strpos($raceArr[1], "右")){
                $raceFile->direction = 2;
            }
        
            // 出馬情報を取得
            $horseCnt = count($doc->find("div.RaceColumn02 div.RaceTableArea tr.HorseList")->elements);
            // 頭数
            $raceFile->horseCount = $horseCnt;

            for($i=0; $i<$horseCnt; $i++){
                $selector = "div.RaceColumn02 div.RaceTableArea tr.HorseList:eq(". $i . ")";

                $info = new ShutsubaInfo();

                $link = $doc->find($selector . " td.HorseInfo span.HorseName a")->attr("href");
                $title = $doc->find($selector . " td.HorseInfo span.HorseName a")->attr("title");
                $linkArr = explode("/", $link);
                $info->horseId = end($linkArr);
                $info->link = $link;
                $info->name = $title;

                $info->waku = $doc->find($selector . " td:eq(0) span")->text();
                $info->umaban = $doc->find($selector . " td:eq(1)")->text();
                $info->age = $doc->find($selector . " td.Barei")->text();
                $info->jockey = $doc->find($selector . " td.Jockey a")->text();
                $info->isCancel = 0;
                $className = $doc->find($selector)->attr("class");
                if(false !== strpos($className, "Cancel")){
                    $info->isCancel = 1;
                }

                $raceFile->shutsubaArray[] = $info;
            }
        }
        catch(\Exception $err){
            return null;
        }

        // 書き込み
        $contents = json_encode($raceFile, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        Storage::disk('public')->put("race/" . $raceId . ".json", $contents);
    }

    /**
     * 競走馬情報の取得
     */
    public static function DownloadHorseInfo($horseId)
    {
        // netkeibaのサイトからhtml情報を取得
        $html = file_get_contents("https://db.netkeiba.com/horse/" . $horseId);
        if(!$html){
            return;
        }
        
        $horseFile = new FileHorse();
        try {
            // htmlを読み込み
            $doc = phpQuery::newDocument($html);

            // ホースID
            $horseFile->horseId = $horseId;
            // 名前
            $horseFile->name = $doc->find('div.horse_title h1')->text();
            // 年齢
            $age = $doc->find('div.horse_title p.txt_01')->text();
            $ageArr = explode("　", $age);
            $horseFile->age = $ageArr[1];
            // 通算成績
            $loop = count($doc->find('div.db_main_deta table.db_prof_table tr')->elements);
            for($i=0; $i<$loop; $i++){
                $th = $doc->find("div.db_main_deta table.db_prof_table tr:eq(". $i . ") th")->text();
                if($th == "通算成績"){
                    $strWork = $doc->find("div.db_main_deta table.db_prof_table tr:eq(". $i . ") td a")->text();
                    $rankArr = explode("-",$strWork);
                    // 1位数
                    $horseFile->rank1Count = intval($rankArr[0]);
                    // 2位数
                    $horseFile->rank2Count = intval($rankArr[1]);
                    // 3位数
                    $horseFile->rank3Count = intval($rankArr[2]);
                    // ランク外
                    $horseFile->rankEtcCount = intval($rankArr[3]);
                    // 通算レース数
                    $horseFile->raceTotal = $horseFile->rank1Count + $horseFile->rank2Count + $horseFile->rank3Count + $horseFile->rankEtcCount;
                }
            }

            // 血統情報
            $bloodIdArr = [];
            $ankArr = $doc->find('div.db_main_deta table.blood_table a')->elements;
            for($i=0; $i<count($ankArr); $i++) {
                $link = $ankArr[$i]->getAttribute("href");
                $linkArr = explode("/", trim($link,'/'));
                $bloodIdArr[] = end($linkArr);
            }
        
            // 競走成績
            $loop = count($doc->find('table.db_h_race_results tbody tr')->elements);
            for($i=0; $i<$loop; $i++){
                $selector = "table.db_h_race_results tbody tr:eq(". $i . ")";

                $history = new RaceHistory();
                $history->date = $doc->find($selector . " td:eq(0) a")->text();
                $history->baba = $doc->find($selector . " td:eq(1) a")->text();
                $history->tenki = $doc->find($selector . " td:eq(2)")->text();
                $history->raceNo = intval($doc->find($selector . " td:eq(3)")->text());
                $history->raceName = $doc->find($selector . " td:eq(4) a")->text();
                $history->horseCount = intval($doc->find($selector . " td:eq(6)")->text());
                $history->waku = intval($doc->find($selector . " td:eq(7)")->text());
                $history->umaban = intval($doc->find($selector . " td:eq(8)")->text());
                $history->odds = $doc->find($selector . " td:eq(9)")->text();
                $history->ninki = intval($doc->find($selector . " td:eq(10)")->text());
                $history->rankNo = $doc->find($selector . " td:eq(11)")->text();
                $history->jockey = $doc->find($selector . " td:eq(12) a")->text();
                $history->kinryo = intval($doc->find($selector . " td:eq(13)")->text());
                $strWork = $doc->find($selector . " td:eq(14)")->text();
                $history->distance = intval(preg_replace('/[^0-9]/', '', $strWork));
                if(false !== strpos($strWork, "芝")){
                    $history->groundType = 1;
                }elseif(false !== strpos($strWork, "ダ")){
                    $history->groundType = 2;
                }else{
                    $history->groundType = 3;
                }
                $history->condition = $doc->find($selector . " td:eq(15)")->text();
                $history->time = $doc->find($selector . " td:eq(17)")->text();
                $history->difference = $doc->find($selector . " td:eq(18)")->text();
                $history->pointTime = $doc->find($selector . " td:eq(20)")->text();
                $strWork = $doc->find($selector . " td:eq(21)")->text();
                $strArr = explode("-", $strWork);
                if(count($strArr) >= 2){
                    $history->firstPace = $strArr[0];
                    $history->latterPace = $strArr[1];
                }else{
                    $history->firstPace = "";
                    $history->latterPace = "";
                }
                $history->agari600m = $doc->find($selector . " td:eq(22)")->text();
                $history->weight = $doc->find($selector . " td:eq(23)")->text();
                $history->winHorse = $doc->find($selector . " td:eq(26) a")->text();

                $horseFile->recodeArray[] = $history;
            }

            // 父の情報
            if(count($bloodIdArr) > 0){
                $parent = NetkeibaUtil::getParentRecode($bloodIdArr[0]);
                if(!is_null($parent)){
                    $horseFile->dadName = $parent->name;
                    $horseFile->dadRecode = $parent->recode;
                    $horseFile->dadWinRate = $parent->winRate;
                    $horseFile->dadPodiumRate = $parent->podiumRate;
                }
            }
            // 父方の祖父の情報
            if(count($bloodIdArr) > 1){
                $parent = NetkeibaUtil::getParentRecode($bloodIdArr[1]);
                if(!is_null($parent)){
                    $horseFile->dadSohuName = $parent->name;
                    $horseFile->dadSohuRecode = $parent->recode;
                    $horseFile->dadSohuWinRate = $parent->winRate;
                    $horseFile->dadSohuPodiumRate = $parent->podiumRate;
                }
            }
            // 父方の祖母の情報
            if(count($bloodIdArr) > 2){
                $parent = NetkeibaUtil::getParentRecode($bloodIdArr[2]);
                if(!is_null($parent)){
                    $horseFile->dadSoboName = $parent->name;
                    $horseFile->dadSoboRecode = $parent->recode;
                    $horseFile->dadSoboWinRate = $parent->winRate;
                    $horseFile->dadSoboPodiumRate = $parent->podiumRate;
                }
            }
            // 母の情報
            if(count($bloodIdArr) > 3){
                $parent = NetkeibaUtil::getParentRecode($bloodIdArr[3]);
                if(!is_null($parent)){
                    $horseFile->mamName = $parent->name;
                    $horseFile->mamRecode = $parent->recode;
                    $horseFile->mamWinRate = $parent->winRate;
                    $horseFile->mamPodiumRate = $parent->podiumRate;
                }
            }
            // 母方の祖父の情報
            if(count($bloodIdArr) > 4){
                $parent = NetkeibaUtil::getParentRecode($bloodIdArr[4]);
                if(!is_null($parent)){
                    $horseFile->mamSohuName = $parent->name;
                    $horseFile->mamSohuRecode = $parent->recode;
                    $horseFile->mamSohuWinRate = $parent->winRate;
                    $horseFile->mamSohuPodiumRate = $parent->podiumRate;
                }
            }
            // 母方の祖母の情報
            if(count($bloodIdArr) > 5){
                $parent = NetkeibaUtil::getParentRecode($bloodIdArr[5]);
                if(!is_null($parent)){
                    $horseFile->mamSoboName = $parent->name;
                    $horseFile->mamSoboRecode = $parent->recode;
                    $horseFile->mamSoboWinRate = $parent->winRate;
                    $horseFile->mamSoboPodiumRate = $parent->podiumRate;
                }
            }

        }
        catch(\Exception $err){
            return null;
        }

        // 書き込み
        $contents = json_encode($horseFile, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        Storage::disk('public')->put("horse/" . $horseId . ".json", $contents);
    }


    /**
     * 親の成績を取得
     */
    private static function getParentRecode($horseId) : FileParentHorse
    {
        $rtnObj = new FileParentHorse();

        try {
            // ファイルから取得
            if(Storage::disk('public')->exists("parent/" . $horseId . ".json")){
                $contents = Storage::disk('public')->get("parent/" . $horseId . ".json");
                $json = json_decode($contents, true);
                $rtnObj->horseId = $json["horseId"];
                $rtnObj->name = $json["name"];
                $rtnObj->recode = $json["recode"];
                $rtnObj->winRate = $json["winRate"];
                $rtnObj->podiumRate = $json["podiumRate"];
            }else{
                // htmlから取得
                $html = file_get_contents("https://db.netkeiba.com/horse/" . $horseId);
                if(!$html){
                    return null;
                }
                $doc = phpQuery::newDocument($html);
                $rtnObj->horseId = $horseId;
                $rtnObj->name = $doc->find('div.horse_title h1')->text();
                // 通算成績
                $loop = count($doc->find('div.db_main_deta table.db_prof_table tr')->elements);
                for($j=0; $j<$loop; $j++){
                    $th = $doc->find("div.db_main_deta table.db_prof_table tr:eq(". $j . ") th")->text();
                    if($th == "通算成績"){
                        $strWork = $doc->find("div.db_main_deta table.db_prof_table tr:eq(". $j . ") td a")->text();
                        $rankArr = explode("-",$strWork);
                        $rank1 = intval($rankArr[0]);
                        $rank2 = intval($rankArr[1]);
                        $rank3 = intval($rankArr[2]);
                        $rankEtc = intval($rankArr[3]);
                        $total = $rank1 + $rank2 + $rank3 + $rankEtc;
                        // 成績
                        $rtnObj->recode = sprintf("[%d-%d-%d-%d]", $rank1, $rank2, $rank3, $rankEtc);
                        if($total > 0){
                            $rtnObj->winRate = round($rank1 / $total * 100);
                            $rtnObj->podiumRate = round(($rank1 + $rank2 + $rank3) / $total * 100);
                        }else{
                            $rtnObj->winRate = 0;
                            $rtnObj->podiumRate = 0;
                        }
                        break;
                    }
                }
                // 書き込み
                $contents = json_encode($rtnObj, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                Storage::disk('public')->put("parent/" . $horseId . ".json", $contents);
            }
        }
        catch(\Exception $err){
            return null;
        }
        return $rtnObj;
    }


    /**
     * 開催場をIDから取得
     */
    private static function getKaisaiName($raceId){
        // 年(4) + 場名(2) + 回数(2) + 日(2) + レースNo(2)
        $babaCode = substr($raceId, 4, 2);
        $cnt = substr($raceId, 6, 2);
        $day = substr($raceId, 8, 2);
        $raceNo = substr($raceId, 10, 2);

        $rtnStr = intval($cnt) . "回";
        $rtnStr .= config("const.BAMEI_NAME")[$babaCode];
        $rtnStr .= intval($day) . "日目 ";
        $rtnStr .= intval($raceNo) . "レース";
        return $rtnStr;
    }


}

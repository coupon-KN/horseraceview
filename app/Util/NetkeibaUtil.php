<?php
namespace App\Util;
use App\Models\FileRaceData;
use App\Models\ShutsubaInfo;
use App\Models\FileHorse;
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
        $race->setJsonData($json);
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
        $horse->setJsonData($json);
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
        // メモ情報
        $babaCode = substr($raceId, 4, 2);
        if(array_key_exists($babaCode, config("const.BABA_MEMO"))){
            if(array_key_exists($raceFile->distance, config("const.BABA_MEMO")[$babaCode])){
                $view->courseMemo = config("const.BABA_MEMO")[$babaCode][$raceFile->distance];
            }
        }

        // 出馬情報
        foreach($raceFile->shutsubaArray As $item)
        {
            $horse = new ViewHorseData();
            $horse->waku = $item->waku;
            $horse->umaban = $item->umaban;
            $horse->name = $item->name;
            $horse->age = $item->age;
            $horse->kinryo = $item->kinryo;
            $horse->jockey = $item->jockey;
            $horse->isCancel = $item->isCancel;
            // 競走馬情報
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
                    //$horse->recodeArray = array_splice($fileHorse->recodeArray, 0, 10);
                    $horse->recodeArray = $fileHorse->recodeArray;
                }else{
                    $horse->recodeArray = [];
                }
                // 親の情報
                $horse->dad = $fileHorse->dad;
                $horse->dadSohu = $fileHorse->dadSohu;
                $horse->dadSobo = $fileHorse->dadSobo;
                $horse->mam = $fileHorse->mam;
                $horse->mamSohu = $fileHorse->mamSohu;
                $horse->mamSobo = $fileHorse->mamSobo;

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
            $raceColumn01 = $doc->find("#page div.RaceColumn01")->getDocument();

            // レースID
            $raceFile->raceId = $raceId;
            // レース名
            $raceFile->name = trim($raceColumn01->find("h1.RaceName")->text());
            $raceFile->name = str_replace(PHP_EOL, "", $raceFile->name);
            // レース情報
            $strRace = $raceColumn01->find("div.RaceData01")->text();
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
            $raceTable = $doc->find("div.RaceColumn02 div.RaceTableArea table")->getDocument();
            $horseCnt = count($raceTable->find("tr.HorseList")->elements);
            // 頭数
            $raceFile->horseCount = $horseCnt;

            for($i=0; $i<$horseCnt; $i++){
                $info = new ShutsubaInfo();
                $link = $raceTable->find("tr.HorseList:eq(" . $i . ") td.HorseInfo span.HorseName a")->attr("href");
                $title = $raceTable->find("tr.HorseList:eq(" . $i . ") td.HorseInfo span.HorseName a")->attr("title");
                $linkArr = explode("/", $link);
                $info->horseId = end($linkArr);
                $info->link = $link;
                $info->name = $title;

                $info->waku = $raceTable->find("tr.HorseList:eq(" . $i . ") td:eq(0) span")->text();
                $info->umaban = $raceTable->find("tr.HorseList:eq(" . $i . ") td:eq(1)")->text();
                $info->age = $raceTable->find("tr.HorseList:eq(" . $i . ") td.Barei")->text();
                $info->kinryo = $raceTable->find("tr.HorseList:eq(" . $i . ") td:eq(5)")->text();
                $info->jockey = $raceTable->find("tr.HorseList:eq(" . $i . ") td.Jockey a")->text();
                $info->isCancel = 0;
                $className = $raceTable->find("tr.HorseList:eq(" . $i . ")")->attr("class");
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
            $profTable = $doc->find('div.db_main_deta table.db_prof_table')->getDocument();
            $loop = count($profTable->find('tr')->elements);
            for($i=0; $i<$loop; $i++){
                $th = $profTable->find("tr:eq(". $i . ") th")->text();
                if($th == "通算成績"){
                    $strWork = $profTable->find("tr:eq(". $i . ") td a")->text();
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
                $row = $doc->find("div.db_main_race div.db_main_deta table tbody tr:eq(" . $i . ")");

                $history = new RaceHistory();
                $history->date = $row->find("td:eq(0) a")->text();
                $history->baba = $row->find("td:eq(1) a")->text();
                $history->tenki = $row->find("td:eq(2)")->text();
                $history->raceNo = intval($row->find("td:eq(3)")->text());
                $history->raceName = $row->find("td:eq(4) a")->text();
                $history->horseCount = intval($row->find("td:eq(6)")->text());
                $history->waku = intval($row->find("td:eq(7)")->text());
                $history->umaban = intval($row->find("td:eq(8)")->text());
                $history->odds = $row->find("td:eq(9)")->text();
                $history->ninki = intval($row->find("td:eq(10)")->text());
                $history->rankNo = $row->find("td:eq(11)")->text();
                $history->jockey = $row->find("td:eq(12) a")->text();
                $history->kinryo = intval($row->find("td:eq(13)")->text());
                $strWork = $row->find("td:eq(14)")->text();
                $history->distance = intval(preg_replace('/[^0-9]/', '', $strWork));
                if(false !== strpos($strWork, "芝")){
                    $history->groundType = 1;
                }elseif(false !== strpos($strWork, "ダ")){
                    $history->groundType = 2;
                }else{
                    $history->groundType = 3;
                }
                $history->condition = $row->find("td:eq(15)")->text();
                $history->time = $row->find("td:eq(17)")->text();
                $history->difference = $row->find("td:eq(18)")->text();
                $history->pointTime = $row->find("td:eq(20)")->text();
                $strWork = $row->find("td:eq(21)")->text();
                $strArr = explode("-", $strWork);
                if(count($strArr) >= 2){
                    $history->firstPace = $strArr[0];
                    $history->latterPace = $strArr[1];
                }else{
                    $history->firstPace = "";
                    $history->latterPace = "";
                }
                $history->agari600m = $row->find("td:eq(22)")->text();
                $history->weight = $row->find("td:eq(23)")->text();
                $history->winHorse = $row->find("td:eq(26) a")->text();

                // ペース区分を計算
                if($history->firstPace != "" && $history->latterPace != ""){
                    $paceDiff = (double)$history->firstPace - (double)$history->latterPace;
                    if($paceDiff <= 1.0 && $paceDiff >= -1.0){
                        $history->paceKbn = "M";
                    }elseif($paceDiff > 1.0){
                        $history->paceKbn = "S";
                    }else{
                        $history->paceKbn = "H";
                    }
                }

                // 過去のレースURLを作成
                if($history->date != "" && $history->baba != "" && $history->raceNo) {
                    // 馬名からコードを取得
                    $babaName = "";
                    $babaCode = "";
                    foreach(config("const.BAMEI_NAME") as $key => $val){
                        if(strpos($history->baba, $val) !== false){
                            $babaCode = $key;
                            $babaName = $val;
                            break;
                        }
                    }

                    if($babaCode != ""){
                        if(in_array($babaCode, config("const.CENTRAL_BAMEI_CODE"))){
                            // 中央競馬
                            $rtnId = date("Y", strtotime($history->date));
                            $rtnId .= substr("00" . preg_replace("/[^0-9]/", "", explode($babaName, $history->baba)[0]), -2);
                            $rtnId .= $babaCode;
                            $rtnId .= substr("00" . preg_replace("/[^0-9]/", "", explode($babaName, $history->baba)[1]), -2);
                            $rtnId .= substr("00" . $history->raceNo, -2);
                            $history->raceUrl = str_replace("{raceid}", $rtnId, config("const.JRA_MOVIE_PAGE"));
                        }
                        else if(array_key_exists($babaCode, config('const.REGION_TRACK_ID'))){
                            // 地方競馬
                            $raceUrl = config("const.REGION_MOVIE_PAGE");
                            $raceUrl = str_replace("{track}", config('const.REGION_TRACK_ID')[$babaCode], $raceUrl);
                            $raceUrl = str_replace("{date}", date("Ymd", strtotime($history->date)), $raceUrl);
                            $raceUrl = str_replace("{raceno}", substr("00" . $history->raceNo, -2), $raceUrl);
                            $history->raceUrl = $raceUrl;
                        }
                    }
                }

                $horseFile->recodeArray[] = $history;
            }

            // 父の情報
            if(count($bloodIdArr) > 0){
                $horseFile->dad = NetkeibaParentHorseUtil::getInstance()->getHorseData($bloodIdArr[0]);
            }
            // 父方の祖父の情報
            if(count($bloodIdArr) > 1){
                $horseFile->dadSohu = NetkeibaParentHorseUtil::getInstance()->getHorseData($bloodIdArr[1]);
            }
            // 父方の祖母の情報
            if(count($bloodIdArr) > 2){
                $horseFile->dadSobo = NetkeibaParentHorseUtil::getInstance()->getHorseData($bloodIdArr[2]);
            }
            // 母の情報
            if(count($bloodIdArr) > 3){
                $horseFile->mam = NetkeibaParentHorseUtil::getInstance()->getHorseData($bloodIdArr[3]);
            }
            // 母方の祖父の情報
            if(count($bloodIdArr) > 4){
                $horseFile->mamSohu = NetkeibaParentHorseUtil::getInstance()->getHorseData($bloodIdArr[4]);
            }
            // 母方の祖母の情報
            if(count($bloodIdArr) > 5){
                $horseFile->mamSobo = NetkeibaParentHorseUtil::getInstance()->getHorseData($bloodIdArr[5]);
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


    /**
     * 地方競馬のレース情報の取得
     */
    public static function DownloadRegionRaceInfo($raceId)
    {
        // netkeibaのサイトからhtml情報を取得
        $html = file_get_contents("https://nar.netkeiba.com/race/shutuba.html?race_id=" . $raceId);
        if(!$html){
            return;
        }

        $html = mb_convert_encoding($html, "UTF-8", "EUC-JP");

        $raceFile = new FileRaceData();
        try {
            $doc = phpQuery::newDocument($html);
            $raceColumn01 = $doc->find("div.RaceColumn01")->getDocument();

            // レースID
            $raceFile->raceId = $raceId;
            // レース名
            $raceFile->name = trim($raceColumn01->find("div.RaceList_Item02 div.RaceName")->text());
            $raceFile->name = str_replace("\n", "", $raceFile->name);
            // レース情報
            $strRace = $raceColumn01->find("div.RaceData01")->text();
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
            $raceTable = $doc->find("div.RaceColumn02 div.RaceTableArea table")->getDocument();
            $horseCnt = count($raceTable->find("tr.HorseList")->elements);
            // 頭数
            $raceFile->horseCount = $horseCnt;

            for($i=0; $i<$horseCnt; $i++){
                $info = new ShutsubaInfo();
                $link = $raceTable->find("tr.HorseList:eq(" . $i . ") td.HorseInfo span.HorseName a")->attr("href");
                $title = $raceTable->find("tr.HorseList:eq(" . $i . ") td.HorseInfo span.HorseName a")->text();
                $linkArr = explode("/", $link);
                $info->horseId = end($linkArr);
                $info->link = $link;
                $info->name = $title;

                $info->waku = $raceTable->find("tr.HorseList:eq(" . $i . ") td:eq(0)")->text();
                $info->umaban = $raceTable->find("tr.HorseList:eq(" . $i . ") td:eq(1)")->text();
                $info->age = $raceTable->find("tr.HorseList:eq(" . $i . ") td:eq(4) span")->text();
                $info->kinryo = $raceTable->find("tr.HorseList:eq(" . $i . ") td:eq(5)")->text();
                $info->jockey = $raceTable->find("tr.HorseList:eq(" . $i . ") td:eq(6) span.Jockey a")->text();
                $info->isCancel = 0;
                $className = $raceTable->find("tr.HorseList:eq(" . $i . ")")->attr("class");
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


}

<?php
namespace App\Util;
use App\Models\RaceData;
use App\Models\HorseData;
use App\Models\HorseHistory;
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
        if (Storage::disk('public')->exists(NetkeibaUtil::$SCHEDULE_FILE)) {
            $json = json_decode(Storage::disk('public')->get(NetkeibaUtil::$SCHEDULE_FILE), true);

            if($date == "all") {
                return $json;
            }
            else if(array_key_exists($date, $json)){
                return $json[$date];
            }
        }
        return [];
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
    public static function getRaceData($raceId) : RaceData {
        $contents = Storage::disk('public')->get("race/" . $raceId . ".json");
        $json = json_decode($contents, true);

        $race = new RaceData();
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

        $raceData = new RaceData();
        try {
            $doc = phpQuery::newDocument($html);
            $raceColumn01 = $doc->find("#page div.RaceColumn01")->getDocument();

            // レースID
            $raceData->raceId = $raceId;
            // 開催場
            $raceData->kaisai = NetkeibaUtil::getKaisaiName($raceId);
            // レース名
            $raceName = trim($raceColumn01->find("h1.RaceName")->text());
            $raceData->raceName = str_replace(PHP_EOL, "", $raceName);
            // レースデータを配列へ
            $strWork = preg_replace('/　|\s+/', '', $raceColumn01->find("div.RaceData01")->text());
            $raceArr = explode("/", $strWork);
            // 発送時刻
            $raceData->startingTime = str_replace("発走", "", $raceArr[0]);
            // 種類
            $type = mb_substr(preg_replace('/　|\s+/', '', $raceArr[1]), 0, 1);
            if($type == "芝"){
                $raceData->groundType = 1;
            }elseif($type == "ダ"){
                $raceData->groundType = 2;
            }elseif($type == "障"){
                $raceData->groundType = 3;
            }
            // 距離
            $raceData->distance = intval(preg_replace('/[^0-9]/', '', $raceArr[1]));
            // 向き
            if(false !== strpos($raceArr[1], "左")){
                $raceData->direction = 1;
            }elseif(false !== strpos($raceArr[1], "右")){
                $raceData->direction = 2;
            }

            // レース等級の取得
            $strClassName = $doc->find("#page div.RaceColumn01 .RaceName .Icon_GradeType")->attr("class");
            $strRace = trim($doc->find("#page div.RaceColumn01 div.RaceData02")->text());
            $raceArr = explode("\n", $strRace);
            $raceData->raceGarade = NetkeibaUtil::getRaceGradeClass($strClassName, $raceArr[4]);

            // 出馬情報を取得
            $raceTable = $doc->find("div.RaceColumn02 div.RaceTableArea table")->getDocument();
            $horseCnt = count($raceTable->find("tr.HorseList")->elements);
            // 頭数
            $raceData->horseCount = $horseCnt;

            for($i=0; $i<$horseCnt; $i++){
                $link = $raceTable->find("tr.HorseList:eq(" . $i . ") td.HorseInfo span.HorseName a")->attr("href");
                $title = $raceTable->find("tr.HorseList:eq(" . $i . ") td.HorseInfo span.HorseName a")->attr("title");
                $linkArr = explode("/", $link);

                $horse = new HorseData();
                // 馬ID
                $horse->horseId = end($linkArr);
                // 枠番
                $horse->waku = $raceTable->find("tr.HorseList:eq(" . $i . ") td:eq(0) span")->text();
                // 馬番
                $horse->umaban = $raceTable->find("tr.HorseList:eq(" . $i . ") td:eq(1)")->text();
                // 名前
                $horse->name = $title;
                /** 年齢 */
                $horse->age = $raceTable->find("tr.HorseList:eq(" . $i . ") td.Barei")->text();
                /** 斤量 */
                $horse->kinryo = $raceTable->find("tr.HorseList:eq(" . $i . ") td:eq(5)")->text();
                /** 騎手 */
                $horse->jockey = $raceTable->find("tr.HorseList:eq(" . $i . ") td.Jockey a")->text();
                /** 除外 */
                $horse->isCancel = 0;
                $className = $raceTable->find("tr.HorseList:eq(" . $i . ")")->attr("class");
                if(false !== strpos($className, "Cancel")){
                    $horse->isCancel = 1;
                }

                NetkeibaUtil::DownloadHorseInfo($horse);
                $raceData->horseArray[] = $horse;
            }

            // レース情報
            $raceData->raceInfo = $raceData->startingTime . "発走 ";
            $raceData->raceInfo .= config("const.GROUND_NAME")[$raceData->groundType];
            $raceData->raceInfo .= $raceData->distance . "m";
            $raceData->raceInfo .= "("  . config("const.DIRECTION_NAME")[$raceData->direction] . ") ";
            $raceData->raceInfo .= $raceData->horseCount . "頭";
            // メモ情報
            $babaCode = substr($raceId, 4, 2);
            if(array_key_exists($babaCode, config("const.BABA_MEMO"))){
                if(array_key_exists($raceData->distance, config("const.BABA_MEMO")[$babaCode])){
                    $raceData->courseMemo = config("const.BABA_MEMO")[$babaCode][$raceData->distance];
                }
            }
        }
        catch(\Exception $err){
            return null;
        }

        // 書き込み
        $contents = json_encode($raceData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        Storage::disk('public')->put("race/" . $raceId . ".json", $contents);
    }

    /**
     * 競走馬情報の取得
     */
    public static function DownloadHorseInfo(HorseData $horseData)
    {
        // netkeibaのサイトからhtml情報を取得
        $html = file_get_contents("https://db.netkeiba.com/horse/" . $horseData->horseId);
        if(!$html){
            return;
        }
        
        try {
            // htmlを読み込み
            $doc = phpQuery::newDocument($html);

            // 通算成績
            $profTable = $doc->find('div.db_main_deta table.db_prof_table')->getDocument();
            $loop = count($profTable->find('tr')->elements);
            for($i=0; $i<$loop; $i++){
                $th = $profTable->find("tr:eq(". $i . ") th")->text();
                if($th == "通算成績"){
                    $strWork = $profTable->find("tr:eq(". $i . ") td a")->text();
                    $rankArr = explode("-", $strWork);
                    // 1位数
                    $rank1Count = intval($rankArr[0]);
                    // 2位数
                    $rank2Count = intval($rankArr[1]);
                    // 3位数
                    $rank3Count = intval($rankArr[2]);
                    // ランク外
                    $rankEtcCount = intval($rankArr[3]);
                    // 通算レース数
                    $raceTotal = $rank1Count + $rank2Count + $rank3Count + $rankEtcCount;

                    $horseData->recode = sprintf("[%d-%d-%d-%d]", $rank1Count, $rank2Count, $rank3Count, $rankEtcCount);
                    if($raceTotal > 0){
                        $horseData->winRate = round($rank1Count / $raceTotal * 100);
                        $horseData->podiumRate = round(($rank1Count + $rank2Count + $rank3Count) / $raceTotal * 100);
                    }else{
                        $horseData->winRate = 0;
                        $horseData->podiumRate = 0;
                    }
                }elseif($th == "獲得賞金"){
                    $strWork = $profTable->find("tr:eq(". $i . ") td")->text();
                    $strWork = str_replace(" ", "", trim($strWork));
                    $horseData->totalPrize = str_replace("/", "\n", trim($strWork));
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

                $history = new HorseHistory();
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
                $history->difference = trim($row->find("td:eq(18)")->text());
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

                            $history->raceGrade = RegionRaceGradeUtil::getInstance()->getRaceGrade($history->date, $babaCode, $history->raceNo);
                        }
                    }
                }

                $horseData->recodeArray[] = $history;
            }

            // 父の情報
            if(count($bloodIdArr) > 0){
                $horseData->dad = NetkeibaParentHorseUtil::getInstance()->getHorseData($bloodIdArr[0]);
            }
            // 父方の祖父の情報
            if(count($bloodIdArr) > 1){
                $horseData->dadSohu = NetkeibaParentHorseUtil::getInstance()->getHorseData($bloodIdArr[1]);
            }
            // 父方の祖母の情報
            if(count($bloodIdArr) > 2){
                $horseData->dadSobo = NetkeibaParentHorseUtil::getInstance()->getHorseData($bloodIdArr[2]);
            }
            // 母の情報
            if(count($bloodIdArr) > 3){
                $horseData->mam = NetkeibaParentHorseUtil::getInstance()->getHorseData($bloodIdArr[3]);
            }
            // 母方の祖父の情報
            if(count($bloodIdArr) > 4){
                $horseData->mamSohu = NetkeibaParentHorseUtil::getInstance()->getHorseData($bloodIdArr[4]);
            }
            // 母方の祖母の情報
            if(count($bloodIdArr) > 5){
                $horseData->mamSobo = NetkeibaParentHorseUtil::getInstance()->getHorseData($bloodIdArr[5]);
            }

        }
        catch(\Exception $err){
            return null;
        }
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

        $raceData = new RaceData();
        try {
            $doc = phpQuery::newDocument($html);
            $raceColumn01 = $doc->find("div.RaceColumn01")->getDocument();

            // レースID
            $raceData->raceId = $raceId;
            // レース名
            $raceName = trim($raceColumn01->find("div.RaceList_Item02 div.RaceName")->text());
            $raceData->raceName = str_replace("\n", "", $raceName);
            // レースデータを配列へ
            $strWork = preg_replace('/　|\s+/', '', $raceColumn01->find("div.RaceData01")->text());
            $raceArr = explode("/", $strWork);
            // 発送時刻
            $raceData->startingTime = str_replace("発走", "", $raceArr[0]);
            // 種類
            $type = mb_substr(preg_replace('/　|\s+/', '', $raceArr[1]), 0, 1);
            if($type == "芝"){
                $raceData->groundType = 1;
            }elseif($type == "ダ"){
                $raceData->groundType = 2;
            }elseif($type == "障"){
                $raceData->groundType = 3;
            }
            // 距離
            $raceData->distance = intval(preg_replace('/[^0-9]/', '', $raceArr[1]));
            // 向き
            if(false !== strpos($raceArr[1], "左")){
                $raceData->direction = 1;
            }elseif(false !== strpos($raceArr[1], "右")){
                $raceData->direction = 2;
            }
        
            // 出馬情報を取得
            $raceTable = $doc->find("div.RaceColumn02 div.RaceTableArea table")->getDocument();
            $horseCnt = count($raceTable->find("tr.HorseList")->elements);
            // 頭数
            $raceData->horseCount = $horseCnt;

            // クラス
            $strRace = trim($doc->find("div.RaceColumn01 div.RaceData02")->text());
            $raceArr = explode("\n", $strRace);
            $raceData->raceGarade = $raceArr[3];
            
            for($i=0; $i<$horseCnt; $i++){
                $link = $raceTable->find("tr.HorseList:eq(" . $i . ") td.HorseInfo span.HorseName a")->attr("href");
                $title = $raceTable->find("tr.HorseList:eq(" . $i . ") td.HorseInfo span.HorseName a")->text();
                $linkArr = explode("/", $link);

                $horse = new HorseData();
                $horse->horseId = end($linkArr);
                $horse->waku = $raceTable->find("tr.HorseList:eq(" . $i . ") td:eq(0)")->text();
                $horse->umaban = $raceTable->find("tr.HorseList:eq(" . $i . ") td:eq(1)")->text();
                $horse->name = $title;
                $horse->age = $raceTable->find("tr.HorseList:eq(" . $i . ") td:eq(4) span")->text();
                $horse->kinryo = $raceTable->find("tr.HorseList:eq(" . $i . ") td:eq(5)")->text();
                $horse->jockey = $raceTable->find("tr.HorseList:eq(" . $i . ") td:eq(6) span.Jockey a")->text();
                $horse->isCancel = 0;
                $className = $raceTable->find("tr.HorseList:eq(" . $i . ")")->attr("class");
                if(false !== strpos($className, "Cancel")){
                    $horse->isCancel = 1;
                }

                NetkeibaUtil::DownloadHorseInfo($horse);
                $raceData->horseArray[] = $horse;
            }
 
            // レース情報
            $raceData->raceInfo = $raceData->startingTime . "発走 ";
            $raceData->raceInfo .= config("const.GROUND_NAME")[$raceData->groundType];
            $raceData->raceInfo .= $raceData->distance . "m";
            $raceData->raceInfo .= "("  . config("const.DIRECTION_NAME")[$raceData->direction] . ") ";
            $raceData->raceInfo .= $raceData->horseCount . "頭";
            // メモ情報
            $babaCode = substr($raceId, 4, 2);
            if(array_key_exists($babaCode, config("const.BABA_MEMO"))){
                if(array_key_exists($raceData->distance, config("const.BABA_MEMO")[$babaCode])){
                    $raceData->courseMemo = config("const.BABA_MEMO")[$babaCode][$raceData->distance];
                }
            }
       }
        catch(\Exception $err){
            return null;
        }

        // 書き込み
        $contents = json_encode($raceData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        Storage::disk('public')->put("race/" . $raceId . ".json", $contents);
    }

    /**
     * 開催馬場情報を取得
     */
    public static function DownloadScheduleData($selDate) {
        $centralArr = NetkeibaUtil::scrapingScheduleData($selDate, "c");
        $regionArr = NetkeibaUtil::scrapingScheduleData($selDate, "r");

        if(count($centralArr) > 0 || count($regionArr) > 0) {
            $json = NetkeibaUtil::getScheduleList("all");
            $json[$selDate] = array_merge($centralArr, $regionArr);
            ksort($json);
            $contents = json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            Storage::disk('public')->put(NetkeibaUtil::$SCHEDULE_FILE,  $contents);
        }
    }
    
    private static function scrapingScheduleData($strYmd, $areaFlg)
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
     * 中央のレース等級を取得
     */
    public static function getRaceGradeClass($className, $raceClass) {
        $keys = array_keys(config("const.RACE_CLASS_RANK"));

        $garade1 = count($keys) - 1;
        $garade1 = (false !== strpos($className, "Icon_GradeType1") ) ? 0 : $garade1;
        $garade1 = (false !== strpos($className, "Icon_GradeType2") ) ? 1 : $garade1;
        $garade1 = (false !== strpos($className, "Icon_GradeType3") ) ? 2 : $garade1;
        $garade1 = (false !== strpos($className, "Icon_GradeType15")) ? 3 : $garade1;
        $garade1 = (false !== strpos($className, "Icon_GradeType5") ) ? 4 : $garade1;
        $garade1 = (false !== strpos($className, "Icon_GradeType16")) ? 5 : $garade1;
        $garade1 = (false !== strpos($className, "Icon_GradeType17")) ? 6 : $garade1;
        $garade1 = (false !== strpos($className, "Icon_GradeType18")) ? 7 : $garade1;

        $garade1 = (false !== strpos($className, "Icon_GradeType10")) ? 0 : $garade1;
        $garade1 = (false !== strpos($className, "Icon_GradeType11")) ? 1 : $garade1;
        $garade1 = (false !== strpos($className, "Icon_GradeType12")) ? 2 : $garade1;

        $garade2 = 0;
        foreach($keys as $val) {
            if(false !== strpos($val, $raceClass)) break;
            $garade2 ++;
        }

        return $keys[min($garade1, $garade2)];
    }

}

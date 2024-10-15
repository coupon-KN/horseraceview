<?php
namespace App\Models;
use App\Models\HorseData;


/**
 * レース情報
 */
class RaceData
{
    /** レースID */
    public $raceId;

    /** 開催場 */
    public $kaisai;

    /** レース名 */
    public $raceName;

    /** 出走時刻 */
    public $startingTime;

    /** 種類(1:芝、2:ダート、3:障害) */
    public $groundType = 0;

    /** 距離 */
    public $distance = 0;

    /** 向き(1:左、2:右) */
    public $direction = 0;

    /** レース等級 */
    public $raceGarade;

    /** 頭数 */
    public $horseCount;

    /** レース情報 */
    public $raceInfo;

    /** コースメモ */
    public $courseMemo;

    /** 競走馬情報配列 HorseData[] */
    public array $horseArray = [];

    /** Jsonデータを設定 */
    public function setJsonData($json) {
        foreach($json as $key => $val){
            if(property_exists($this, $key)){
                if(is_array($this->{$key})){
                    if($key == "horseArray"){
                        foreach($val as $row) {
                            $horse = new HorseData();
                            $horse->setJsonData($row);
                            $this->{$key}[] = $horse;
                        }
                    }
                }else{
                    $this->{$key} = $val;
                }
            }
        }
    }

}

<?php
namespace App\Models;
use App\Models\ShutsubaInfo;


/**
 * レース情報ファイル
 */
class FileRaceData
{
    /** レースID */
    public $raceId;

    /** レース名 */
    public $name;

    /** 発送時刻 */
    public $startingTime;

    /** 種類(1:芝、2:ダート、3:障害) */
    public $groundType = 0;

    /** 距離 */
    public $distance = 0;

    /** 向き(1:左、2:右) */
    public $direction = 0;

    /** 頭数 */
    public $horseCount;

    /** レースクラス */
    public $raceGarade;

    /** 出馬情報配列 */
    public array $shutsubaArray = [];

    /** Jsonデータを設定 */
    public function setJsonData($json) {
        foreach($json as $key => $val){
            if(property_exists($this, $key)){
                if(is_array($this->{$key})){
                    if($key == "shutsubaArray"){
                        foreach($val as $row) {
                            $shutsuba = new ShutsubaInfo();
                            $shutsuba->setJsonData($row);
                            $this->{$key}[] = $shutsuba;
                        }
                    }
                }else{
                    $this->{$key} = $val;
                }
            }
        }
    }

}

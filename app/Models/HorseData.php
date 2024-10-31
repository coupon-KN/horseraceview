<?php
namespace App\Models;
use App\Models\ParentHorse;
use App\Models\HorseHistory;


/**
 * 競走馬情報
 */
class HorseData
{
    /** 馬ID */
    public $horseId;
    /** 枠番 */
    public $waku;
    /** 馬番 */
    public $umaban;
    /** 名前 */
    public $name;
    /** 年齢 */
    public $age;
    /** 斤量 */
    public $kinryo;
    /** 騎手 */
    public $jockey;
    /** 除外 */
    public $isCancel;
    /** 成績 */
    public $recode;
    /** 獲得賞金 */
    public $totalPrize;
    /** 勝率 */
    public $winRate;
    /** 複勝率 */
    public $podiumRate;
    /** ユーザーコメント */
    public $userComment = "";

    /** 父親の情報 */
    public ParentHorse $dad;
    /** 父方の祖父の情報 */
    public ParentHorse $dadSohu;
    /** 父方の祖母の情報 */
    public ParentHorse $dadSobo;
    /** 母親の情報 */
    public ParentHorse $mam;
    /** 母方の祖父の情報 */
    public ParentHorse $mamSohu;
    /** 母方の祖母の情報 */
    public ParentHorse $mamSobo;

    /** 履歴 TestHorseHistory */
    public array $recodeArray = [];


    /** Jsonデータを設定 */
    public function setJsonData($json) {
        foreach($json as $key => $val){
            if(property_exists($this, $key)){
                if($key == "dad" || $key == "dadSohu" || $key == "dadSobo" || $key == "mam" || $key == "mamSohu" || $key == "mamSobo"){
                    $parent = new ParentHorse();
                    $parent->setJsonData($val);
                    $this->{$key} = $parent;
                }
                elseif($key == "recodeArray"){
                    if(is_array($this->{$key})){
                        foreach($val as $row) {
                            $history = new HorseHistory();
                            $history->setJsonData($row);
                            $this->{$key}[] = $history;
                        }
                    }
                }else{
                    $this->{$key} = $val;
                }
            }
        }
    }

}

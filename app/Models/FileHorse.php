<?php
namespace App\Models;
use App\Models\RaceHistory;
use App\Models\ParentHorse;


/**
 * 競走馬情報ファイル
 */
class FileHorse
{
    /** 馬ID */
    public $horseId;

    /** 名前 */
    public $name;

    /** 年齢 */
    public $age;

    /** 通算レース数 */
    public $raceTotal;

    /** 1位数 */
    public $rank1Count;

    /** 2位数 */
    public $rank2Count;

    /** 3位数 */
    public $rank3Count;

    /** ランク外 */
    public $rankEtcCount;

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

    /** 成績 */
    public array $recodeArray = [];

    /** Jsonデータを設定 */
    public function setJsonData($json) {
        foreach($json as $key => $val){
            if(property_exists($this, $key)){
                switch($key){
                    case "dad":
                    case "dadSohu":
                    case "dadSobo":
                    case "mam":
                    case "mamSohu":
                    case "mamSobo":
                        $this->{$key} = new ParentHorse();
                        $this->{$key}->setJsonData($val);
                        break;
                    case "recodeArray":
                        foreach($val as $row) {
                            $history = new RaceHistory();
                            $history->setJsonData($row);
                            $this->{$key}[] = $history;
                        }
                        break;
                    default:
                        $this->{$key} = $val;
                }
            }
        }
    }

}

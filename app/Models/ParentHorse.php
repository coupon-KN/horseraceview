<?php
namespace App\Models;

/**
 * 親馬の情報ファイル
 */
class ParentHorse
{
    /** 馬ID */
    public $horseId;

    /** 名前 */
    public $name;

    /** 成績 */
    public $recode;

    /** 勝率 */
    public $winRate;

    /** 複勝率 */
    public $podiumRate;

    /** Jsonデータを設定 */
    public function setJsonData($json) {
        foreach($json as $key => $val){
            if(property_exists($this, $key)){
                $this->{$key} = $val;
            }
        }
    }
}

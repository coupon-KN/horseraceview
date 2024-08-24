<?php
namespace App\Models;

/**
 * 出馬情報クラス
 */
class ShutsubaInfo
{
    /** 馬ID */
    public $horseId;

    /** 情報リンク */
    public $link;
    
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

    /** 除外フラグ */
    public $isCancel;

    /** Jsonデータを設定 */
    public function setJsonData($json) {
        foreach($json as $key => $val){
            if(property_exists($this, $key)){
                $this->{$key} = $val;
            }
        }
    }
}

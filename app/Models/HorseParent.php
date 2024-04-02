<?php
namespace App\Models;


/**
 * 競走馬の親情報格納クラス
 */
class HorseParent
{
    /** 名前 */
    public $name;

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
}

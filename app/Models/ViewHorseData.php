<?php
namespace App\Models;


/**
 * 表示用競走馬情報
 */
class ViewHorseData
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
    /** 騎手 */
    public $jockey;
    /** 除外 */
    public $isCancel;
    /** 成績 */
    public $recode;
    /** 勝率 */
    public $winRate;
    /** 複勝率 */
    public $podiumRate;

    /** 父親名 */
    public $dadName;
    /** 父(成績) */
    public $dadRecode;
    /** 父(勝率) */
    public $dadWinRate;
    /** 父(複勝率) */
    public $dadPodiumRate;

    /** 父方の祖父名 */
    public $dadSohuName;
    /** 父方の祖父(成績) */
    public $dadSohuRecode;
    /** 父方の祖父(勝率) */
    public $dadSohuWinRate;
    /** 父方の祖父(複勝率) */
    public $dadSohuPodiumRate;

    /** 父方の祖母名 */
    public $dadSoboName;
    /** 父方の祖母(成績) */
    public $dadSoboRecode;
    /** 父方の祖母(勝率) */
    public $dadSoboWinRate;
    /** 父方の祖母(複勝率) */
    public $dadSoboPodiumRate;

    /** 母親名 */
    public $mamName;
    /** 母(成績) */
    public $mamRecode;
    /** 母(勝率) */
    public $mamWinRate;
    /** 母(複勝率) */
    public $mamPodiumRate;
    
    /** 母方の祖父名 */
    public $mamSohuName;
    /** 母方の祖父(成績) */
    public $mamSohuRecode;
    /** 母方の祖父(勝率) */
    public $mamSohuWinRate;
    /** 母方の祖父(複勝率) */
    public $mamSohuPodiumRate;

    /** 母方の祖母名 */
    public $mamSoboName;
    /** 母方の祖母(成績) */
    public $mamSoboRecode;
    /** 母方の祖母(勝率) */
    public $mamSoboWinRate;
    /** 母方の祖母(複勝率) */
    public $mamSoboPodiumRate;

    /** 履歴 */
    public $recodeArray = [];

}

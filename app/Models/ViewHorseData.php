<?php
namespace App\Models;
use App\Models\ParentHorse;


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
    /** 斤量 */
    public $kinryo;
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

    /** 履歴 */
    public $recodeArray = [];

}

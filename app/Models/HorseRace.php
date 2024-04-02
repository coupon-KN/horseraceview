<?php
namespace App\Models;


/**
 * レース情報格納クラス
 */
class HorseRace
{
    /** レースID */
    public $id;

    /** レース名 */
    public $name;

    /** 種類(1:芝、2:ダート、3:障害) */
    public $groundType = 0;

    /** 距離 */
    public $distance = 0;

    /** 向き(1:左、2:右) */
    public $direction = 0;

    /** 出馬配列 */
    public $horseArray = [];
}

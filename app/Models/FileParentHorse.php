<?php
namespace App\Models;

/**
 * 親情報ファイル
 */
class FileParentHorse
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
}

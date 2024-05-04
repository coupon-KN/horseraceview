/**
 * 競馬に関するインターフェース
 */

// スケジュール
export interface IRaceScheduleItem {
    id : string;
    name : string
}

// レース情報
export interface IViewRaceData {
    /** レースID */
    raceId : string;
    /** 開催場 */
    kaisai : string;
    /** レース名 */
    raceName : string;
    /** レース情報 */
    raceInfo : string;
    /** 出走時刻 */
    startingTime : string;
    /** 種類(1:芝、2:ダート、3:障害) */
    groundType : number;
    /** 距離 */
    distance : number;
    /** 向き(1:左、2:右) */
    direction : number;
    /** 頭数 */
    horseCount : number;
    /** 競走馬情報配列 */
    horseArray : IViewHorseData[];

};

// 競走馬情報
export interface IViewHorseData {
    /** 馬ID */
    horseId : string;
    /** 枠番 */
    waku : number;
    /** 馬番 */
    umaban : number;
    /** 名前 */
    name : string;
    /** 年齢 */
    age : string;
    /** 騎手 */
    jockey : string;
    /** 除外フラグ */
    isCancel : Boolean;
    /** 成績 */
    recode : string;
    /** 勝率 */
    winRate : string;
    /** 複勝率 */
    podiumRate : string;

    /** 父親名 */
    dadName : string;
    /** 父(成績) */
    dadRecode : string;
    /** 父(勝率) */
    dadWinRate : string;
    /** 父(複勝率) */
    dadPodiumRate : string;

    /** 父方の祖父名 */
    dadSohuName : string;
    /** 父方の祖父(成績) */
    dadSohuRecode : string;
    /** 父方の祖父(勝率) */
    dadSohuWinRate : string;
    /** 父方の祖父(複勝率) */
    dadSohuPodiumRate : string;

    /** 父方の祖母名 */
    dadSoboName : string;
    /** 父方の祖母(成績) */
    dadSoboRecode : string;
    /** 父方の祖母(勝率) */
    dadSoboWinRate : string;
    /** 父方の祖母(複勝率) */
    dadSoboPodiumRate : string;

    /** 母親名 */
    mamName : string;
    /** 母(成績) */
    mamRecode : string;
    /** 母(勝率) */
    mamWinRate : string;
    /** 母(複勝率) */
    mamPodiumRate : string;
    
    /** 母方の祖父名 */
    mamSohuName : string;
    /** 母方の祖父(成績) */
    mamSohuRecode : string;
    /** 母方の祖父(勝率) */
    mamSohuWinRate : string;
    /** 母方の祖父(複勝率) */
    mamSohuPodiumRate : string;

    /** 母方の祖母名 */
    mamSoboName : string;
    /** 母方の祖母(成績) */
    mamSoboRecode : string;
    /** 母方の祖母(勝率) */
    mamSoboWinRate : string;
    /** 母方の祖母(複勝率) */
    mamSoboPodiumRate : string;

    /** 履歴 */
    recodeArray : IRaceHistory[];
}

// レース履歴
export interface IRaceHistory {
    /** 日付 */
    date : string;
    /** 開催馬場 */
    baba : string;
    /** 天気 */
    tenki : string;
    /** レースNo */
    raceNo : number;
    /** レース名 */
    raceName : string;
    /** 頭数 */
    horseCount : number;
    /** 枠番 */
    waku : number;
    /** 馬番 */
    umaban : number;
    /** オッズ */
    odds : number;
    /** 人気 */
    ninki : number;
    /** 着順 */
    rankNo : string;
    /** 騎手 */
    jockey : string;
    /** 斤量 */
    kinryo : number;
    /** 距離 */
    distance : number;
    /** 種類(1:芝、2:ダート、3:障害) */
    groundType : number;
    /** 種類略称(1:芝、2:ダ、3:障) */
    groundShortName : string;
    /** 馬場状態 */
    condition : string;
    /** タイム */
    time : string;
    /** 着差 */
    difference : string;
    /** 通過 */
    pointTime : string;
    /** ペース(前半3ハロン) */
    firstPace : string;
    /** ペース(後半3ハロン) */
    latterPace : string;
    /** 上り3ハロン */
    agari600m : string;
    /** 馬体重 */
    weight : string;
    /** 勝ち馬 */
    winHorse : string;
}

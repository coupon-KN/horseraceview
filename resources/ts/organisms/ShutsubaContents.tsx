import React, { useState } from "react";
import "./ShutsubaContents.scss"
import * as constants from "../constants";
import RaceHistoryTable from "../molecules/RaceHistoryTable";
import ParentHorseCell from "../molecules/ParentHorseCell";
import { IRaceScheduleItem, IViewRaceData, IScoringItem } from "../interface/IHorseRace";
import axios, { AxiosResponse }  from "axios";

type Props = {
    setLoginMethod : any;
    setLoadingMethod : any;
}


/**
 * 出馬情報コンテンツ
 */
const ShutsubaContents = (props:Props) => {
    const [targetDate, setTargetDate] = useState(new Date().toLocaleDateString('sv-SE'));
    const [selectRaceId, setSelectRaceId] = useState("");
    const [scheduleArr, setScheduleArr] = useState<IRaceScheduleItem[]>();
    const [raceData, setRaceData] = useState<IViewRaceData>();
    const [markArray, setMarkArray] = useState<string[]>([]);
    const [scoreArray, setScoreArray] = useState<string[]>([]);
    const [openRowArray, setOpenRowArray] = useState<boolean[]>([]);
    const [isBloodline, setIsBloodline] = useState(false);
    const [isComment, setIsComment] = useState(false);


    React.useEffect(() => {
        getSchedule(targetDate);
    }, []);

    /**
     * 日付変更イベント
     */
    const changeDateHandle : React.ChangeEventHandler<HTMLInputElement> = async(e) => {
        setTargetDate(e.target.value);
        getSchedule(e.target.value);
    }

    /**
     * スケジュールデータ取得APIをコール
     * @param selDate 
     */
    const getSchedule = async (selDate : string) => {
        props.setLoadingMethod(true);
        try{
            await axios.post(constants.HR_SCHDULE_URL, {sel_date : selDate}, {withCredentials: true})
            .then((response : AxiosResponse<IRaceScheduleItem[]>) => {
                setScheduleArr(response.data);
                setRaceData(undefined);
            })
            .catch((error) => {
                console.log(error);
            });
        }
        catch(error) {
            console.log(error);
        }
        finally{
            props.setLoadingMethod(false);
        }
    }

    /**
     * スケジュールチェンジイベント
     * @param e 
     */
    const changeRaceIdHandle : React.ChangeEventHandler<HTMLSelectElement> = async(e) => {
        let searchRaceId = e.target.value;
        props.setLoadingMethod(true);
        try{
            await axios.post(constants.HR_RACEDATA_URL, {race_id : searchRaceId}, {withCredentials: true})
            .then((response : AxiosResponse<IViewRaceData>) => {
                if(response.status == 200){
                    // Cookieからマーク情報取得
                    setMarkArray(getMarkCookie(searchRaceId, response.data.horseCount));
                    setRaceData(response.data);
                    // スコアを初期化
                    let scoreTempArray = new Array<string>(response.data.horseCount);
                    for(let i=0; i<scoreTempArray.length; i++){
                        scoreTempArray[i] = "";
                    }
                    setScoreArray(scoreTempArray);
    
                    // 行を非表示
                    let closeArray = new Array<boolean>(response.data.horseCount);
                    for(let i=0; i<closeArray.length; i++){
                        closeArray[i] = false;
                    }
                    setOpenRowArray(closeArray);
    
                }else{
                    setRaceData(undefined);
                }
                console.log(response);
            })
            .catch((error) => {
                console.log(error);
            });
        }
        catch(error) {
            console.log(error);
        }
        finally{
            props.setLoadingMethod(false);
            setSelectRaceId(searchRaceId);
        }
    }

    /**
     * 印セルクリックイベント
     * @param event 
     */
    const MarkCellClickHandle = (event:React.MouseEvent<HTMLTableCellElement>) => {
        const cell = event.currentTarget as HTMLTableCellElement;

        // マーク変更
        const markArr = ["", "◎", "〇", "▲", "△", "☆", "消"];
        let currentMark = cell.innerText;
        cell.innerText = "";
        for(let i=0; i<markArr.length-1; i++) {
            if(currentMark == markArr[i]){
                cell.innerText = markArr[i+1];
                break;
            }
        }
        // マーク保存
        let index = Number(cell.dataset["index"]);
        markArray[index] = cell.innerText;
        setMarkArray(markArray);
        saveMarkCookie(selectRaceId, markArray.join(","));

        // 背景制御
        let parentRow = event.currentTarget.parentElement as HTMLTableRowElement;
        if(cell.innerText == "消"){
            parentRow.className = parentRow.className.replace("normal", "delete");
        }else{
            parentRow.className = parentRow.className.replace("delete", "normal");
        }
    }

    /**
     * 名前セルクリックイベント
     * @param event 
     */
    const NameCellClickHandle = (index:number) => {
        let copyArray = openRowArray.concat();
        copyArray[index] = !copyArray[index];
        setOpenRowArray(copyArray);
    }

    /**
     * AllOpenボタンクリックイベント
     */
    const AllOpenClickHandle = () => {
        let copyArray = openRowArray.concat();
        for(let i=0; i<copyArray.length; i++){
            copyArray[i] = true;
        }
        setOpenRowArray(copyArray);
    }

    /**
     * マーク情報の取得
     * @param name 
     */
    const getMarkCookie = (cookieName:string, arrLength : number) =>{
        let markArr = new Array<string>(arrLength);
        let cookies = document.cookie;
        if(cookies != ""){
            let content = "";
            cookies.split(';').forEach(function(value) {
                let keyVal = value.trim().split('=');
                if(keyVal[0] == cookieName){
                    content = keyVal[1];
                    return true;
                }
            });
            if(content != ""){
                let array = content.split(',');
                let maxCnt = Math.min(arrLength, array.length);
                for(let i=0; i<maxCnt; i++){
                    markArr[i] = array[i];
                }
            }
        }
        return markArr;
    }

    /**
     * マーク情報の保存
     * @param name 
     */
    const saveMarkCookie = (name : string, value:string) =>{
        document.cookie = name + "=" + value + "; max-age=259200";
    }

    /**
     * 採点APIの呼び出し
     */
    const ScoringClickHandle = async () => {
        props.setLoadingMethod(true);
        try{
            await axios.post(constants.HR_SCORING_URL, {race_id : selectRaceId}, {withCredentials: true})
            .then((response : AxiosResponse<IScoringItem[]>) => {
                if(response.status == 200){
                    let scoreTempArray = new Array<string>(response.data.length);
                    for(let i=0; i<scoreTempArray.length; i++){
                        scoreTempArray[i] = response.data[i].score + "点";
                    }
                    setScoreArray(scoreTempArray);
                }
                console.log(response);
            })
            .catch((error) => {
                console.log(error);
            });
        }
        catch(error) {
            console.log(error);
        }
        finally{
            props.setLoadingMethod(false);
        }
    }
    

    return (
        <div className="shutsuba">
            <div className="w-100 p-2 condtions">
                <input type="date" className="p-1" value={targetDate} onChange={changeDateHandle} />
                <select className="p-1" onChange={changeRaceIdHandle}>
                    { scheduleArr && scheduleArr.map((value) => 
                        <option value={value.id} key={value.id}>{value.name}</option>
                    )}
                </select>
            </div>

            <div className="contents">
                {raceData !== undefined ?
                <>
                    <div className="mb-3">
                        <div className="fs-3">{raceData.raceName}
                            <span className="fs-5">{"(" + raceData.raceGarade + ")"}</span>
                        </div>
                        <div>{raceData.raceInfo}</div>
                        {raceData.courseMemo != "" ?
                            <div>{raceData.courseMemo}</div>
                        : <></>}
                    </div>
                    <table className="w-100 table-bordered tbl-race">
                        <thead className="text-center">
                            <tr className="fs12">
                                <th>枠</th>
                                <th>馬<br />番</th>
                                <th>印</th>
                                <th>馬名</th>
                            </tr>
                        </thead>
                        <tbody>
                            {raceData.horseArray.map((h,index) =>
                            <>
                                {h.isCancel ? 
                                    <tr key={self.crypto.randomUUID()} className="delete">
                                        <td className={"text-center fs12 waku"+h.waku}>{h.waku}</td>
                                        <td className="text-center fs12">{h.umaban}</td>
                                        <td className="text-center" data-index={index}>除外</td>
                                        <td className="ps-1 pe-1">
                                            <div className="d-inline-block w-75">{h.name}</div>
                                            <div className="d-inline-block w-25 fs12">{h.age}</div>
                                            <div className="d-inline-block w-100 fs12">
                                                <span>{h.jockey}</span>
                                                <span>({h.kinryo})</span>
                                                <span className="float-end">{h.recode}&nbsp;&nbsp;勝{h.winRate}%&nbsp;&nbsp;複勝{h.podiumRate}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                :
                                <>
                                    <tr key={self.crypto.randomUUID()} className={markArray[index] == "消" ? "delete" : "normal"}>
                                        <td className={"text-center fs12 waku"+h.waku}>{h.waku}</td>
                                        <td className="text-center fs12">{h.umaban}</td>
                                        <td className="text-center fs-2" data-index={index} onClick={(ev) => {MarkCellClickHandle(ev)}}>{markArray[index]}</td>
                                        <td className="ps-1 pe-1" onClick={() => {NameCellClickHandle(index)}}>
                                            <div className="d-inline-block w-75">{h.name}</div>
                                            <div className="d-inline-block w-25 fs12">{h.age + "　" + (scoreArray[index])}</div>
                                            <div className="d-inline-block w-100 fs12">
                                                <span>{h.jockey}</span>
                                                <span>({h.kinryo})</span>
                                                <span className="float-end">{h.recode}&nbsp;&nbsp;勝{h.winRate}%&nbsp;&nbsp;複勝{h.podiumRate}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                    {isComment && h.userComment != '' ?
                                    <tr key={self.crypto.randomUUID()}>
                                        <td colSpan={4} className="px-2 bg-light">{h.userComment}</td>
                                    </tr>
                                    : ""}
                                    <tr key={self.crypto.randomUUID()} className={openRowArray[index] ? "open" : "close"}>
                                        <td colSpan={4} className="ps-2 bg-light">
                                            {isBloodline ? <ParentHorseCell horseData={h} /> : ""}
                                            <RaceHistoryTable history={h.recodeArray.slice(0,10)} />
                                        </td>
                                    </tr>
                                </>
                                }
                            </>
                            )}
                        </tbody>
                    </table>
                </>
                : "データがありません"}
            </div>
            <footer>
                <a className={"btn-bloodline " + (isBloodline ? "bloodline-on" : "bloodline-off")} onClick={() => setIsBloodline(!isBloodline)}></a>
                <a className="btn-door" onClick={AllOpenClickHandle}></a>
                <a className="btn-analysis" onClick={ScoringClickHandle}></a>
                <a className={"btn-comment " + (isComment ? "comment-on" : "comment-off")} onClick={() => setIsComment(!isComment)}></a>
            </footer>
        </div>
    );

};

export default ShutsubaContents;

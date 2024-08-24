/**
 * 競走馬ページ
 */
import React, { useState } from "react";
import "./HorseRacePage.scss"
import RaceHistoryTable from "../molecules/RaceHistoryTable";
import ParentHorseCell from "../molecules/ParentHorseCell";
import HorseSpinner from "../molecules/HorseSpinner";
import { IRaceScheduleItem, IViewRaceData } from "../interface/IHorseRace";
import axios, { AxiosResponse }  from "axios";

//const HOST_URL = "http://127.0.0.1:8000";
const HOST_URL = "https://chestnut-rice.sakuraweb.com";
const SCHDULE_URL = HOST_URL + "/api/horserace/schdule";
const HORSE_RACE_URL = HOST_URL + "/api/horserace/racedata";


const HorseRacePage = () => {
    const [isLoading, setIsLoading] = useState(false);
    const [selectRaceId, setSelectRaceId] = useState("");
    const [scheduleArr, setScheduleArr] = useState<IRaceScheduleItem[]>();
    const [raceData, setRaceData] = useState<IViewRaceData>();
    const [markArray, setMarkArray] = useState<string[]>([]);
    const [openRowArray, setOpenRowArray] = useState<boolean[]>([]);
    const [isBloodline, setIsBloodline] = useState(false);


    React.useEffect(() => {
        setIsLoading(true);
        getSchedule();
    }, []);

    // スケジュールデータをAPIから取得
    const getSchedule = async () => {
        try{
            setIsLoading(true);
            await axios.post(SCHDULE_URL)
            .then((response : AxiosResponse<IRaceScheduleItem[]>) => {
                setScheduleArr(response.data);
            })
            .catch((error) => {
                console.log(error);
            });
        }
        catch(error) {
            console.log(error);
        }
        finally{
            setIsLoading(false);
        }
    }

    /**
     * スケジュールチェンジイベント
     * @param e 
     */
    const changeRaceIdHandle : React.ChangeEventHandler<HTMLSelectElement> = async(e) => {
        let searchRaceId = e.target.value;
        setIsLoading(true);
        try{
            await axios.post(HORSE_RACE_URL, {race_id : searchRaceId})
            .then((response : AxiosResponse<IViewRaceData>) => {
                if(response.status == 200){
                    // Cookieからマーク情報取得
                    setMarkArray(getMarkCookie(searchRaceId, response.data.horseCount));
                    setRaceData(response.data);
    
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
            setIsLoading(false);
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


    return (
    <div className="horse-race">
        <div className="w-100 p-2 condtions">
            <select onChange={changeRaceIdHandle}>
                { scheduleArr && scheduleArr.map((value) => 
                    <option value={value.id} key={value.id}>{value.name}</option>
                )}
            </select>
        </div>

        <div className="contents">
            {raceData !== undefined ?
            <>
                <div className="mb-3">
                    <div className="fs-2">{raceData.raceName}</div>
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
                                            <span className="float-end">{h.recode}&nbsp;&nbsp;勝{h.winRate}%&nbsp;&nbsp;複勝{h.podiumRate}%</span>
                                        </div>
                                    </td>
                                </tr>
                            :
                            <>
                                <tr key={self.crypto.randomUUID()} className={markArray[index] == "消" ? "delete" : "normal"}>
                                    <td className={"text-center fs12 waku"+h.waku}>{h.waku}</td>
                                    <td className="text-center fs12">{h.umaban}</td>
                                    <td className="text-center fs-1" data-index={index} onClick={(ev) => {MarkCellClickHandle(ev)}}>{markArray[index]}</td>
                                    <td className="ps-1 pe-1" onClick={() => {NameCellClickHandle(index)}}>
                                        <div className="d-inline-block w-75">{h.name}</div>
                                        <div className="d-inline-block w-25 fs12">{h.age}</div>
                                        <div className="d-inline-block w-100 fs12">
                                            <span>{h.jockey}</span>
                                            <span className="float-end">{h.recode}&nbsp;&nbsp;勝{h.winRate}%&nbsp;&nbsp;複勝{h.podiumRate}%</span>
                                        </div>
                                    </td>
                                </tr>
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
            <a className="btn-allopen" onClick={AllOpenClickHandle}>全て開く</a>
        </footer>

        <HorseSpinner isLoading={isLoading} />
    </div>
    );
};

export default HorseRacePage;

/**
 * 競走馬ページ
 */
import React, { useRef, useState } from "react";
import "./HorseRacePage.scss"
import RaceHistoryTable from "../molecules/RaceHistoryTable";
import ParentHorseCell from "../molecules/ParentHorseCell";
import { IRaceScheduleItem, IViewRaceData } from "../interface/IHorseRace";
import axios, { AxiosResponse }  from "axios";

//const SCHDULE_URL = "http://127.0.0.1:8000/api/horserace/schdule";
//const HORSE_RACE_URL = "http://127.0.0.1:8000/api/horserace/racedata";
const SCHDULE_URL = "https://chestnut-rice.sakuraweb.com/api/horserace/schdule";
const HORSE_RACE_URL = "https://chestnut-rice.sakuraweb.com/api/horserace/racedata";

const HorseRacePage = () => {
    const [isLoading, setIsLoading] = useState(true);
    const [selectRaceId, setSelectRaceId] = useState("");
    const [scheduleArr, setScheduleArr] = useState<IRaceScheduleItem[]>();
    const [raceData, setRaceData] = useState<IViewRaceData>();
    const [paddockArray, setPaddockArray] = useState<string[]>([]);
    const [markArray, setMarkArray] = useState<string[]>([]);
    const [isBloodline, setIsBloodline] = useState(false);
    const tableRef = useRef<HTMLTableElement>(null);

    React.useEffect(() => {
        setIsLoading(true);
        getSchedule();
    }, []);

    // スケジュールデータをAPIから取得
    const getSchedule = async () => {
        try{
            setIsLoading(true);
            let response: AxiosResponse<IRaceScheduleItem[]> = await axios.post(SCHDULE_URL);
            setScheduleArr(response.data);
        }
        catch(error) {
            console.error(error);
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
        try{
            setIsLoading(true);
            console.log(selectRaceId);
            let response: AxiosResponse<IViewRaceData> = await axios.post(HORSE_RACE_URL, {race_id : searchRaceId});
            if(response.status == 200){
                // Cookieからマーク情報取得
                setMarkArray(getMarkCookie(searchRaceId + "m", response.data.horseCount));
                setPaddockArray(getMarkCookie(searchRaceId + "p", response.data.horseCount));
                setRaceData(response.data);
            }else{
                setRaceData(undefined);
            }
            console.log(response);
        }
        catch(error) {
            console.error(error);
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
    const MarkCellClickHandle = (event:React.MouseEvent<HTMLTableCellElement>, type:string) => {
        const cell = event.currentTarget as HTMLTableCellElement;
        if(cell.innerText == ""){
            cell.innerText = "◎";
        }else if(cell.innerText == "◎"){
            cell.innerText = "〇";
        }else if(cell.innerText == "〇"){
            cell.innerText = "▲";
        }else if(cell.innerText == "▲"){
            cell.innerText = "△";
        }else if(cell.innerText == "△"){
            cell.innerText = "☆";
        }else if(cell.innerText == "☆"){
            cell.innerText = "消";
        }else{
            cell.innerText = "";
        }
        // 背景制御
        if(type === "m"){
            let parentRow = event.currentTarget.parentElement as HTMLTableRowElement;
            if(cell.innerText == "消"){
                parentRow.className = parentRow.className.replace("normal", "delete");
            }else{
                parentRow.className = parentRow.className.replace("delete", "normal");
            }
        }

        let index = Number(cell.dataset["index"]);
        let array;
        if(type === "m"){
            array = markArray;
            array[index] = cell.innerText;
            setMarkArray(array);
        }else{
            array = paddockArray;
            array[index] = cell.innerText;
            setPaddockArray(array);
        }
        saveMarkCookie(selectRaceId + type, array.join(","));
    }
    /**
     * 名前セルクリックイベント
     * @param event 
     */
    const NameCellClickHandle = (event:React.MouseEvent<HTMLTableCellElement>) => {
        const tableRow = event.currentTarget.parentElement as HTMLTableRowElement;
        let nextTableRow = tableRow.nextElementSibling as HTMLTableRowElement;

        if(nextTableRow != null){
            if(nextTableRow.checkVisibility()){
                nextTableRow.className = nextTableRow.className.replace("active", "passive");
            }else{
                nextTableRow.className = nextTableRow.className.replace("passive", "active");
            }
        }
    }
    /**
     * AllOpenボタンクリックイベント
     */
    const AllOpenClickHandle = () => {
        if(tableRef != null){
            const rows = tableRef.current?.getElementsByClassName("passive");
            if(rows !== undefined && rows.length > 0){
                for (let i=rows.length - 1; i>=0; i--) {
                    rows[i].className = rows[i].className.replace("passive", "active");
                }
            }
        }
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

        <div className="p-1 pt-2">
            {raceData !== undefined ?
            <>
                <div className="mb-3">
                    <div className="fs-2">{raceData.raceName}</div>
                    <div>{raceData.raceInfo}</div>
                </div>
                <table className="w-100 table-bordered tbl-race" ref={tableRef}>
                    <thead className="text-center">
                        <tr className="fs12">
                            <th>枠</th>
                            <th>馬<br />番</th>
                            <th>パド<br />ック</th>
                            <th>印</th>
                            <th>馬名</th>
                        </tr>
                    </thead>
                    <tbody>
                        {raceData.horseArray.map((h,index) =>
                        <>
                            {h.isCancel ? 
                                <tr key={index} className="delete">
                                    <td className={"text-center fs12 waku"+h.waku}>{h.waku}</td>
                                    <td className="text-center fs12">{h.umaban}</td>
                                    <td colSpan={2} className="text-center fs-1" data-index={index}>除外</td>
                                    <td className="ps-1 pe-1" onClick={(ev) => {NameCellClickHandle(ev)}}>
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
                                <tr key={index} className={markArray[index] == "消" ? "delete" : "normal"}>
                                    <td className={"text-center fs12 waku"+h.waku}>{h.waku}</td>
                                    <td className="text-center fs12">{h.umaban}</td>
                                    <td className="text-center fs-1" data-index={index} onClick={(ev) => {MarkCellClickHandle(ev,"p")}}>{paddockArray[index]}</td>
                                    <td className="text-center fs-1" data-index={index} onClick={(ev) => {MarkCellClickHandle(ev,"m")}}>{markArray[index]}</td>
                                    <td className="ps-1 pe-1" onClick={(ev) => {NameCellClickHandle(ev)}}>
                                        <div className="d-inline-block w-75">{h.name}</div>
                                        <div className="d-inline-block w-25 fs12">{h.age}</div>
                                        <div className="d-inline-block w-100 fs12">
                                            <span>{h.jockey}</span>
                                            <span className="float-end">{h.recode}&nbsp;&nbsp;勝{h.winRate}%&nbsp;&nbsp;複勝{h.podiumRate}%</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr key={index + 100} className="passive">
                                    <td colSpan={5} className="ps-2 bg-light">
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

        {isLoading ? <div className="loading"></div> : ""}
    </div>
    );
};

export default HorseRacePage;

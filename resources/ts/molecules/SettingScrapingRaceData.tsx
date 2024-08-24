/**
 * 管理ページ
 */
import React, { useState,useRef } from "react";
import "./SettingScrapingRaceData.scss"
import axios, { AxiosResponse }  from "axios";
import { ISelectItem,IRaceListItem } from "../interface/IHorseRace";


//const HOST_URL = "http://127.0.0.1:8000";
const HOST_URL = "https://chestnut-rice.sakuraweb.com";
const KAISAI_BABA_URL = HOST_URL + "/api/horserace/kaisaibaba";
const RACE_LIST_URL = HOST_URL + "/api/horserace/racelist";
const SCRAPING_RACE_URL = HOST_URL + "/api/horserace/scrapingracedata";

type Props = {
    setLoginMethod : any;
    setLoadingMethod : any;
}


const SettingScrapingRaceData = (props:Props) => {
    const [targetDate, setTargetDate] = useState(new Date().toLocaleDateString('sv-SE'));
    const [babaArr, setBabaArr] = useState<ISelectItem[]>();
    const [raceList, setRaceList] = useState<IRaceListItem[]>([]);
    const tableRef = useRef<HTMLTableElement>(null);


    React.useEffect(() => {
        getBabaData(targetDate);
    }, []);


    /**
     * 日付変更イベント
     * @param e 
     */
    const changeDateHandle : React.ChangeEventHandler<HTMLInputElement> = async(e) => {
        setTargetDate(e.target.value);
        getBabaData(e.target.value);
    }

    /**
     * 馬場情報の取得
     * @param selDate 
     */
    const getBabaData = async(selDate : string) => {
        props.setLoadingMethod(true);
        setRaceList([]);
        try{
            await axios.post(KAISAI_BABA_URL, {sel_date : selDate}, { withCredentials: true})
            .then((response:AxiosResponse<ISelectItem[]>) => {
                setBabaArr(response.data);
            })
            .catch((error) => {
                if(error.response.status == 401){
                    props.setLoginMethod(false);
                }else{
                    console.log(error);
                }
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
     * 馬場変更イベント
     * @param e 
     */
    const changeBabaHandle : React.ChangeEventHandler<HTMLSelectElement> = async(e) => {
        let raceId = e.target.value;
        setRaceList([]);
        if(raceId != "" && targetDate != ""){
            props.setLoadingMethod(true);
            try{
                await axios.post(RACE_LIST_URL, {sel_date : targetDate, race_id : raceId}, { withCredentials: true})
                .then((response:AxiosResponse<IRaceListItem[]>) => {
                    setRaceList(response.data);
                })
                .catch((error) => {
                    if(error.response.status == 401){
                        props.setLoginMethod(false);
                    }else{
                        console.log(error);
                    }
                });
            }
            catch(error) {
                console.error(error);
            }
            finally{
                props.setLoadingMethod(false);
            }
        }
    }

    /**
     * レース情報取得
     * @param e 
     */
    const scrapingRaceHandle : React.MouseEventHandler<HTMLButtonElement> = async(e) => {
        const raceId = e.currentTarget.dataset.raceId as string;
        listItemStatusChange(raceId, 2);
        props.setLoadingMethod(true);
        await callScrapingRaceData(raceId);
        props.setLoadingMethod(false);
    }

    /**
     * 一括取得
     */
    const bulkScrapingHandle = async() => {
        if(tableRef != null){
            const buttons = tableRef.current?.getElementsByClassName("btn-scraping");
            if(buttons !== undefined && buttons.length > 0){
                props.setLoadingMethod(true);
                for (let i=0; i<buttons.length; i++) {
                    const btn = buttons[i] as HTMLButtonElement;
                    let raceId = btn.dataset.raceId as string;
                    listItemStatusChange(raceId, 2);
                    await callScrapingRaceData(btn.dataset.raceId as string);
                }
                props.setLoadingMethod(false);
            }
        }
    }

    /**
     * スクレイピングAPIをコール
     */
    const callScrapingRaceData = async(raceId : string) => {
        try{
            await axios.post(SCRAPING_RACE_URL, {race_id : raceId}, { withCredentials: true})
            .then((response:AxiosResponse<IRaceListItem>) => {
                if(response.data.status == 1){
                    for(let i=0; i<raceList.length; i++){
                        if(raceList[i].id == response.data.id){
                            raceList[i] = response.data;
                            break;
                        }
                    }
                    let copyArray = raceList.concat();
                    setRaceList(copyArray);
                }
            })
            .catch((error) => {
                if(error.response.status == 401){
                    props.setLoginMethod(false);
                }else{
                    console.log(error);
                }
            });
        }
        catch(error) {
            console.error(error);
        }
    }

    /**
     * ステータス変更
     */
    const listItemStatusChange = (raceId : string, status:number) => {
        for(let i=0; i<raceList.length; i++){
            if(raceList[i].id == raceId){
                raceList[i].status = status;
                break;
            }
        }
        let copyArray = raceList.concat();
        setRaceList(copyArray);
    }
    

    return (
        <div className="kanri-scraping-race">
            <div>
                <label className="form-label w-100">対象日
                    <input type="date" className="form-control" value={targetDate} onChange={changeDateHandle} />
                </label>
                <label className="form-label w-100">馬場
                    <select className="form-control" onChange={changeBabaHandle}>
                        <option value=""></option>
                        { babaArr && babaArr.map((row) => <option value={row.key} key={row.key}>{row.value}</option>) }
                    </select>
                </label>
            </div>
            {raceList.length > 0 ? 
            <div>
                <table className="table table-bordered tbl-race-list" ref={tableRef}>
                    <thead className="text-center">
                        <tr>
                            <th>No</th>
                            <th>名称</th>
                            <th>状況</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        {raceList.map((row, index) =>
                        <tr key={self.crypto.randomUUID()}>
                            <td>{index+1}</td>
                            <td>{row.name}</td>
                            <td>
                                {row.status == 0 ? <span className="text-danger">未</span> :
                                    row.status == 1 ? <span className="text-success">済</span> :
                                    <div className="waiting"></div>
                                }
                            </td>
                            <td>
                                <button type="button" className="btn btn-primary btn-sm btn-scraping"
                                    data-race-id={row.id} onClick={(ev) => scrapingRaceHandle(ev)}
                                >取得</button>
                            </td>
                        </tr>
                        )}
                    </tbody>
                </table>
                <button type="button" className="btn btn-primary w-100 mt-3" onClick={bulkScrapingHandle}>一括取得</button>
            </div>
            : ""}
        </div>
    );

};

export default SettingScrapingRaceData;

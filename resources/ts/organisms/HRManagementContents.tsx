import React, { useState,useRef } from "react";
import "./HRManagementContents.scss"
import * as constants from "../constants";
import axios, { AxiosResponse }  from "axios";
import { ISelectItem,IRaceListItem } from "../interface/IHorseRace";

type Props = {
    setLoginMethod : any;
    setLoadingMethod : any;
}


/**
 * 競馬管理コンテンツ
 */
const HRManagementContents = (props:Props) => {
    const [targetDate, setTargetDate] = useState(new Date().toLocaleDateString('sv-SE'));
    const [babaArr, setBabaArr] = useState<ISelectItem[]>();
    const [raceList, setRaceList] = useState<IRaceListItem[]>([]);
    const tableRef = useRef<HTMLTableElement>(null);


    React.useEffect(() => {
        getBabaData(targetDate);
    }, []);


    /**
     * 日付変更イベント
     */
    const changeDateHandler : React.ChangeEventHandler<HTMLInputElement> = async(e) => {
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
            await axios.post(constants.HR_KAISAI_BABA_URL, {sel_date : selDate}, { withCredentials: true})
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
    const changeBabaHandler : React.ChangeEventHandler<HTMLSelectElement> = async(e) => {
        let raceId = e.target.value;
        setRaceList([]);
        if(raceId != "" && targetDate != ""){
            props.setLoadingMethod(true);
            try{
                await axios.post(constants.HR_RACE_LIST_URL, {sel_date : targetDate, race_id : raceId}, { withCredentials: true})
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
     * 開催馬場情報をスクレイピング
     */
    const scrapingKaisaiBabaHandler : React.MouseEventHandler<HTMLButtonElement> = async() => {
        props.setLoadingMethod(true);
        try{
            await axios.post(constants.HR_SCRAPING_KAISAIBABA_URL, {sel_date : targetDate}, { withCredentials: true})
            .then((response:AxiosResponse<ISelectItem[]>) => {
                setBabaArr(response.data);
                props.setLoadingMethod(false);
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
            props.setLoadingMethod(false);
        }
    }
    
    /**
     * レース情報取得
     * @param e 
     */
    const scrapingRaceHandler : React.MouseEventHandler<HTMLButtonElement> = async(e) => {
        const raceId = e.currentTarget.dataset.raceId as string;
        listItemStatusChange(raceId, 2);
        props.setLoadingMethod(true);
        await callScrapingRaceData(raceId);
        props.setLoadingMethod(false);
    }

    /**
     * 一括取得
     */
    const bulkScrapingHandler = async() => {
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
            await axios.post(constants.HR_SCRAPING_RACE_URL, {race_id : raceId}, { withCredentials: true})
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
        <div className="hr-management px-3">
            <label className="form-label w-100">対象日
                <input type="date" className="form-control" value={targetDate} onChange={changeDateHandler} />
            </label>
            { babaArr && babaArr.length > 0 ?
                <label className="form-label w-100">開催馬場
                    <select className="form-control" onChange={changeBabaHandler}>
                        <option value=""></option>
                        { babaArr.map((row) => <option value={row.key} key={row.key}>{row.value}</option>) }
                    </select>
                </label>
            : 
            <>
                <label className="form-label w-100">開催馬場</label>
                <button type="button" className="btn btn-primary btn-sm" onClick={scrapingKaisaiBabaHandler}>開催情報を取得</button>
            </>
            }

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
                                    data-race-id={row.id} onClick={(ev) => scrapingRaceHandler(ev)}
                                >取得</button>
                            </td>
                        </tr>
                        )}
                    </tbody>
                </table>
                <button type="button" className="btn btn-primary w-100 mt-3" onClick={bulkScrapingHandler}>一括取得</button>
            </div>
            : ""}

        </div>
    );
};

export default HRManagementContents;

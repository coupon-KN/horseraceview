/**
 * 管理ページ
 */
import React, { useState } from "react";
import "./SettingRaceScedule.scss"
import axios, { AxiosResponse }  from "axios";
import { ISettingSchedule, ISettingScheduleItem } from "../interface/IHorseRace";


//const HOST_URL = "http://127.0.0.1:8000";
const HOST_URL = "https://chestnut-rice.sakuraweb.com";
const SETTING_SCHEDULE_URL = HOST_URL + "/api/horserace/setting/schedule";
const SCHEDULE_UPDATE_URL = HOST_URL + "/api/horserace/setting/schedule/update";
const WEEK_ARRAY = ['日','月','火', '水', '木', '金', '土'];

type Props = {
    setLoginMethod : any;
    setLoadingMethod : any;
}


const SettingRaceScedule = (props:Props) => {
    const [targetDate, setTargetDate] = useState(new Date().toLocaleDateString('sv-SE'));
    const [sceduleData, setSceduleData] = useState<ISettingSchedule>();


    React.useEffect(() => {
        getScheduleData(targetDate);
    }, []);


    /**
     * 日付変更イベント
     * @param e 
     */
    const changeDateHandler : React.ChangeEventHandler<HTMLInputElement> = async(e) => {
        setTargetDate(e.target.value);
        getScheduleData(e.target.value);
    }

    /**
     * スケジュールデータの取得
     * @param selDate
     */
    const getScheduleData = async(selDate : string) => {
        props.setLoadingMethod(true);
        try{
            await axios.post(SETTING_SCHEDULE_URL, {sel_date : selDate}, { withCredentials: true})
            .then((response : AxiosResponse<ISettingSchedule>) => {
                setSceduleData(response.data);
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
     * 中央競馬追加イベント
     * @param e 
     */
    const AddCentralClickHandler : React.MouseEventHandler<HTMLButtonElement> = (e) => {
        let selBaba = document.getElementById("cenBaba") as HTMLSelectElement;
        let inpNo = document.getElementById("cenNo") as HTMLInputElement;
        let inpDay = document.getElementById("cenDay") as HTMLInputElement;
        let inpNum = document.getElementById("cenNum") as HTMLInputElement;

        if(selBaba.value != "" && inpNo.value != "" && inpDay.value != "" && inpNum.value != ""){
            var regexp = new RegExp(/^[0-9]+(\.[0-9]+)?$/);
            if(!regexp.test(inpNo.value) || !regexp.test(inpDay.value) || !regexp.test(inpNum.value)){
                alert("数値を入力してください");
                return;
            }

            let id = (new Date(targetDate)).getFullYear().toString()
                + selBaba.value
                + ("0" + inpNo.value).slice(-2)
                + ("0" + inpDay.value).slice(-2);
            let name = inpNo.value + "回 "
                + selBaba.options[selBaba.selectedIndex].label + " "
                + inpDay.value + "日目";

            if(sceduleData !== undefined){
                sceduleData.schedule.push({id:id, name : name, num : Number(inpNum.value)});
                UpdateSchedule(sceduleData.schedule);
            }
        }
    }

    /**
     * 地方競馬追加イベント
     * @param e 
     */
    const AddRegionClickHandler : React.MouseEventHandler<HTMLButtonElement> = (e) => {
        let selBaba = document.getElementById("regBaba") as HTMLSelectElement;
        let inpNum = document.getElementById("regNum") as HTMLInputElement;

        if(selBaba.value != "" && inpNum.value != ""){
            var regexp = new RegExp(/^[0-9]+(\.[0-9]+)?$/);
            if(!regexp.test(inpNum.value)){
                alert("数値を入力してください");
                return;
            }

            let dt = new Date(targetDate);
            let id = dt.getFullYear()
                + selBaba.value
                + ("0" + (dt.getMonth() + 1)).slice(-2)
                + ("0" + dt.getDate()).slice(-2);
            let name = selBaba.options[selBaba.selectedIndex].label + " "
                + (dt.getMonth() + 1) + "月"
                + dt.getDate() + "日("
                + WEEK_ARRAY[dt.getDay()] + ")";

            if(sceduleData !== undefined){
                sceduleData.schedule.push({id:id, name : name, num : Number(inpNum.value)});
                UpdateSchedule(sceduleData.schedule);
            }
        }
    }

    /**
     * スケジュール更新
     * @param updObj
     */
    const UpdateSchedule = async(updObj : ISettingScheduleItem[]) => {
        props.setLoadingMethod(true);
        try{
            await axios.post(SCHEDULE_UPDATE_URL, {sel_date : targetDate, schedule : JSON.stringify(updObj)}, { withCredentials: true})
            .then((response : AxiosResponse<ISettingSchedule>) => {
                setSceduleData(response.data);
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
     * スケジュール削除ボタン
     * @param e 
     */
    const deleteScheduleHandler : React.MouseEventHandler<HTMLAnchorElement> = async(e) => {
        let anchor = e.currentTarget as HTMLAnchorElement;
        let index = Number(anchor.dataset["index"]);

        if(sceduleData !== undefined){
            sceduleData.schedule.splice(index,1);
            UpdateSchedule(sceduleData.schedule);
        }
    }



    return (
        <div className="kanri-race-scdule">
            <label className="form-label w-100">対象日
                <input type="date" className="form-control" value={targetDate} onChange={changeDateHandler} />
            </label>
            {sceduleData != undefined && sceduleData.central.length > 0 ?
            <div>
                <span>スケジュール</span>
                <table className="table table-bordered">
                    <thead className="text-center">
                        <tr>
                            <th style={{width:"auto"}}>名称</th>
                            <th style={{width:"60px"}}>ﾚｰｽ数</th>
                            <th style={{width:"60px"}}>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        {sceduleData.schedule.map((row,index) =>
                        <tr>
                            <td>{row.name}</td>
                            <td>{row.num}</td>
                            <td className="text-center">
                                <a href="#" data-index={index} onClick={deleteScheduleHandler}>削除</a>
                            </td>
                        </tr>
                        )}
                    </tbody>
                </table>

                <div>中央開催情報の追加</div>
                <div className="py-2">
                    <label className="form-label me-2 d-inline-block">馬場
                        <select id="cenBaba" className="form-control" style={{width:"70px"}}>
                            <option></option>
                            { sceduleData.central.map((item) => 
                                <option value={item.key} key={item.key}>{item.value}</option>
                            )}
                        </select>
                    </label>
                    <label className="form-label me-2 d-inline-block">回数
                        <input type="text" inputMode="numeric" id="cenNo" className="form-control" style={{width:"60px"}} />
                    </label>
                    <label className="form-label me-2 d-inline-block">日目
                        <input type="text" inputMode="numeric" id="cenDay" className="form-control" style={{width:"60px"}} />
                    </label>
                    <label className="form-label me-2 d-inline-block">レース数
                        <input type="text" inputMode="numeric" id="cenNum" className="form-control" style={{width:"60px"}} />
                    </label>
                    <button type="button" className="btn btn-primary btn-sm" onClick={AddCentralClickHandler}>追加</button>
                </div>

                <div className="mt-3">地方開催情報の追加</div>
                <div className="py-2">
                    <label className="form-label me-2 d-inline-block">馬場
                        <select id="regBaba" className="form-control" style={{width:"70px"}}>
                            <option></option>
                            { sceduleData.region.map((item) => 
                                <option value={item.key} key={item.key}>{item.value}</option>
                            )}
                        </select>
                    </label>
                    <label className="form-label me-2 d-inline-block">レース数
                        <input type="text" inputMode="numeric" id="regNum" className="form-control" style={{width:"60px"}} />
                    </label>
                    <button type="button" className="btn btn-primary btn-sm" onClick={AddRegionClickHandler}>追加</button>
                </div>

            </div>
            : <></>}

        </div>
    );

};

export default SettingRaceScedule;

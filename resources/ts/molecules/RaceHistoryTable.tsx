/**
 * 競馬情報テーブル
 */
import React, { useState } from 'react';
import "./RaceHistoryTable.scss"
import { IRaceHistory } from "../interface/IHorseRace";

type Props = {
    history? : IRaceHistory[];
}

const RaceHistoryTable = (props : Props) => {
    const historyArray = props.history;
    const DISP_ROW_CNT = 5;

    /**
     * moreクリックイベント
     * @param event 
     */
    const MoreClickHandle = (event:React.MouseEvent<HTMLAnchorElement>) => {
        const anchor = event.currentTarget as HTMLAnchorElement;
        anchor.innerText = (anchor.innerText == "more") ? "close" : "more";

        const divRow = event.currentTarget.parentElement as HTMLDivElement;
        Array.from(divRow.childNodes).forEach((child)=> {
            let div = child as HTMLDivElement;
            if(div.className.indexOf("more-row") == -1){
                div.className = div.className.replace("disp-more", "more-row");
            }else{
                div.className = div.className.replace("more-row", "disp-more");
            }
        });
    }

    
    return (
    <div className="race-history-table bg-white">
        {historyArray != null ?
            <>
                {historyArray.map((h,index) =>
                <div key={index} className={"row m-0 border fs12 rank" + h.rankNo + (index < DISP_ROW_CNT ? "" : " more-row")}>
                    <div className="col-4 p-1">
                        <div>{h.date}</div>
                        <div>{h.baba}</div>
                        <div><a href={h.raceUrl} target='_blank'>{h.raceName}</a></div>
                        <div>{h.groundShortName + h.distance + " " + h.condition}</div>
                    </div>
                    <div className="col-1 p-1 fs-4">
                        <div>{h.rankNo}</div>
                    </div>
                    <div className="col-7 p-1">
                        <div>
                            <span className='w-50 d-inline-block'>{h.jockey}</span>
                            <span className='w-25 d-inline-block'>{h.kinryo}</span>
                            <span className='w-25 d-inline-block'>{h.weight}</span>
                        </div>
                        <div>
                            <span className='w-100 d-inline-block'>{h.horseCount + "頭　" + h.umaban + "番　" + h.ninki + "人気(" + h.odds + ")"}</span>
                        </div>
                        <div>
                            <span className='w-100 d-inline-block'>{h.time + " (" + h.difference + ")　" + h.firstPace + " - " + h.latterPace + " (" + h.agari600m + ")"}</span>
                        </div>
                        <div>{h.pointTime}</div>
                        <div>{h.winHorse}</div>
                    </div>
                </div>
                )}
                {(historyArray.length > DISP_ROW_CNT) ?
                    <a className='w-100 my-2 text-center d-inline-block' onClick={MoreClickHandle}>more</a>
                : ""}

            </>
        : ""}
    </div>
    );
};

export default RaceHistoryTable;
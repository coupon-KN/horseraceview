/**
 * 競走馬の親情報セル
 */
import React, { useState } from 'react';
import { IViewHorseData } from "../interface/IHorseRace";

type Props = {
    horseData : IViewHorseData;
}

const ParentHorseCell = (props : Props) => {
    const horse = props.horseData;

    return (
    <div className="row m-0 fs12">
        <div className="col-6 border bg-dad">
            <div>{horse.dadName}</div>
            <div>{horse.dadRecode}&nbsp;勝{horse.dadWinRate}%&nbsp;複{horse.dadPodiumRate}%</div>
        </div>
        <div className="col-6">
            <div className="row">
                <div className="col-12 border bg-dad">
                    <div>{horse.dadSohuName}</div>
                    <div>{horse.dadSohuRecode}&nbsp;勝{horse.dadSohuWinRate}%&nbsp;複{horse.dadSohuPodiumRate}%</div>
                </div>
                <div className="col-12 border bg-mam">
                    <div>{horse.dadSoboName}</div>
                    <div>{horse.dadSoboRecode}&nbsp;勝{horse.dadSoboWinRate}%&nbsp;複{horse.dadSoboPodiumRate}%</div>
                </div>
            </div>
        </div>
        <div className="col-6 border bg-mam">
            <div>{horse.mamName}</div>
            <div>{horse.mamRecode}&nbsp;勝{horse.mamWinRate}%&nbsp;複{horse.mamPodiumRate}%</div>
        </div>
        <div className="col-6">
            <div className="row">
                <div className="col-12 border bg-dad">
                    <div>{horse.mamSohuName}</div>
                    <div>{horse.mamSohuRecode}&nbsp;勝{horse.mamSohuWinRate}%&nbsp;複{horse.mamSohuPodiumRate}%</div>
                </div>
                <div className="col-12 border bg-mam">
                    <div>{horse.mamSoboName}</div>
                    <div>{horse.mamSoboRecode}&nbsp;勝{horse.mamSoboWinRate}%&nbsp;複{horse.mamSoboPodiumRate}%</div>
                </div>
            </div>
        </div>
    </div>
    );
};

export default ParentHorseCell;
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
            <div>{horse.dad.name}</div>
            <div>{horse.dad.recode}&nbsp;勝{horse.dad.winRate}%&nbsp;複{horse.dad.podiumRate}%</div>
        </div>
        <div className="col-6">
            <div className="row">
                <div className="col-12 border bg-dad">
                    <div>{horse.dadSohu.name}</div>
                    <div>{horse.dadSohu.recode}&nbsp;勝{horse.dadSohu.winRate}%&nbsp;複{horse.dadSohu.podiumRate}%</div>
                </div>
                <div className="col-12 border bg-mam">
                    <div>{horse.dadSobo.name}</div>
                    <div>{horse.dadSobo.recode}&nbsp;勝{horse.dadSobo.winRate}%&nbsp;複{horse.dadSobo.podiumRate}%</div>
                </div>
            </div>
        </div>
        <div className="col-6 border bg-mam">
            <div>{horse.mam.name}</div>
            <div>{horse.mam.recode}&nbsp;勝{horse.mam.winRate}%&nbsp;複{horse.mam.podiumRate}%</div>
        </div>
        <div className="col-6">
            <div className="row">
                <div className="col-12 border bg-dad">
                    <div>{horse.mamSohu.name}</div>
                    <div>{horse.mamSohu.recode}&nbsp;勝{horse.mamSohu.winRate}%&nbsp;複{horse.mamSohu.podiumRate}%</div>
                </div>
                <div className="col-12 border bg-mam">
                    <div>{horse.mamSobo.name}</div>
                    <div>{horse.mamSobo.recode}&nbsp;勝{horse.mamSobo.winRate}%&nbsp;複{horse.mamSobo.podiumRate}%</div>
                </div>
            </div>
        </div>
    </div>
    );
};

export default ParentHorseCell;
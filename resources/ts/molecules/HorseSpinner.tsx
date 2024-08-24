/**
 * 競走馬の親情報セル
 */
import React from 'react';
import "./HorseSpinner.scss"

type Props = {
    isLoading : boolean;
}

const HorseSpinner = (props : Props) => {
    if(props.isLoading){
        return(
            <div className="horse-spinner"></div>
        );
    }else{
        return (<></>);
    }
};

export default HorseSpinner;
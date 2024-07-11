/**
 * 競馬メモページ
 */
import React, { useState } from "react";
import "./HorseMemoPage.scss"


const HorseMemoPage = () => {

    React.useEffect(() => {
    }, []);


    return (
    <div className="horse-memo p-2">
        <h5>大井競馬場</h5>
        <div className="ps-4 pb-4">
            <div>1000m、1400m、2400m：内枠が断然有利</div>
            <div>1200m：内枠の逃げ馬は有利</div>
            <div>1500m：内枠の先行馬は有利</div>
            <div>1600m、2600m：内枠がやや有利</div>
            <div>1700m：内枠が有利・外枠の先行馬は不利</div>
            <div>1800m、2000m：枠順による有利不利はない</div>
        </div>
        <h5>川崎競馬場</h5>
        <div className="ps-4 pb-4">
            <div>900m：内枠が有利</div>
            <div>1400m：内枠の先行馬は有利</div>
            <div>1500m：内枠がやや有利</div>
            <div>1600m、2100m：枠順による有利不利はない</div>
            <div>2000m：内枠が有利</div>
        </div>
        <h5>浦和競馬場</h5>
        <div className="ps-4 pb-4">
            <div>800m：外枠がやや有利</div>
            <div>1300m：内枠が有利</div>
            <div>1400m、1900m：内枠がやや有利</div>
            <div>1500m：真ん中から外枠がやや有利</div>
            <div>1600m：内枠が断然有利</div>
            <div>2000m：枠順による有利不利はない</div>
        </div>
        <h5>船橋競馬場</h5>
        <div className="ps-4 pb-4">
            <div>1000m：スタート五部なら内枠が有利</div>
            <div>1200m、1600m、1700m、1800m、2400m：枠順による有利不利はない</div>
            <div>1400m：内枠がやや不利、先行馬は有利</div>
            <div>1500m、2000m：内枠がやや有利</div>
        </div>
    </div>
    );
};

export default HorseMemoPage;

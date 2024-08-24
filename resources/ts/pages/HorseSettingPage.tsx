/**
 * 管理ページ
 */
import React, { useState,useRef } from "react";
import "./HorseSettingPage.scss"
import axios, { AxiosResponse }  from "axios";
import SettingScrapingRaceData from "../molecules/SettingScrapingRaceData";
import SettingRaceScedule from "../molecules/SettingRaceScedule";
import HorseSpinner from "../molecules/HorseSpinner";


//const HOST_URL = "http://127.0.0.1:8000";
const HOST_URL = "https://chestnut-rice.sakuraweb.com";
const LOGIN_CHECK_URL = HOST_URL + "/api/horserace/logincheck";
const LOGIN_URL = HOST_URL + "/api/horserace/login";
const TAB_RACEDATA = 0;
const TAB_SCHEDULE = 1;


const HorseSettingPage = () => {
    const [isLoading, setIsLoading] = useState(true);
    const [isLogin, setIsLogin] = useState(false);
    const [userId, setUserId] = useState("");
    const [userPass, setUserPass] = useState("");
    const [selectTab, setSelectTab] = useState(TAB_RACEDATA);


    React.useEffect(() => {
        loginCheck();
    }, []);


    /**
     * ログインチェック
     */
    const loginCheck = async() => {
        setIsLoading(true);
        try{
            await axios.post(LOGIN_CHECK_URL,[], { withCredentials: true})
            .then((response) => {
                setIsLogin(response.status == 200);
            })
            .catch((error) => {
                if(error.response.status == 401){
                }else{
                    console.log(error);
                }
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
     * ログインボタン
     */
    const loginButtonHandle = async() => {
        setIsLoading(true);
        try{
            axios.post(LOGIN_URL, {user_id : userId, password : userPass})
            .then( (response) =>{
                if(response.data.token != ""){
                    document.cookie = "chestnut-token=" + response.data.token + ";";
                    setIsLogin(true);
                }
            })
            .catch((error) => {
                console.log(error);
            });
        }
        catch(error) {
            console.error(error);
        }
        finally{
            setIsLoading(false);
        }
    }

    /**
     * タブクリック
     * @param tabID
     */
    const tabClickHandler = (event:React.MouseEvent<HTMLAnchorElement>) => {
        const items = document.getElementsByClassName("nav-link");
        if(items !== undefined && items.length > 0){
            for (let i=items.length - 1; i>=0; i--) {
                items[i].className = items[i].className.replace("active", "");
            }
        }
        event.currentTarget.className += " active";
        setSelectTab(Number(event.currentTarget.dataset.index));
    }


    if(isLogin){
        return (
            <div className="horse-kanri">
                <div className="w-100 p-3">
                    <ul className="nav nav-tabs mb-2">
                        <li className="nav-item">
                            <a className="nav-link active" href="#" data-index={TAB_RACEDATA} onClick={(e) => tabClickHandler(e)}>レース情報取得</a>
                        </li>
                        <li className="nav-item">
                            <a className="nav-link" href="#" data-index={TAB_SCHEDULE} onClick={(e) => tabClickHandler(e)}>スケジュール変更</a>
                        </li>
                    </ul>
                    {selectTab == TAB_RACEDATA ? <SettingScrapingRaceData setLoginMethod={setIsLogin} setLoadingMethod={setIsLoading} /> :
                     selectTab == TAB_SCHEDULE ? <SettingRaceScedule setLoginMethod={setIsLogin} setLoadingMethod={setIsLoading} /> :
                     <></>
                    }
                </div>

                <HorseSpinner isLoading={isLoading} />
            </div>
        );
    }else{
        return (
            <div className="horse-login w-100">
                <div className="mx-auto login-block">
                    <div className="pt-4">
                        <label className="form-label w-100">ID
                            <input type="text" className="form-control" value={userId} onChange={(e) => setUserId(e.target.value)} />
                        </label>
                        <label className="form-label w-100">パスワード
                            <input type="password" className="form-control" value={userPass} onChange={(e) => setUserPass(e.target.value)} />
                        </label>
                    </div>
                    <div className="pt-4">
                        <button type="button" className="btn btn-primary btn-lg w-100" onClick={loginButtonHandle}>ログイン</button>
                    </div>
                </div>

                <HorseSpinner isLoading={isLoading} />
            </div>
        );
    }

};

export default HorseSettingPage;

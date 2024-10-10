import React, { useState } from "react";
import "./HorseRacePage.scss"
import * as constants from "../constants";
import ShutsubaContents from "../organisms/ShutsubaContents";
import HRManagementContents from "../organisms/HRManagementContents";
import HorseSpinner from "../molecules/HorseSpinner";
import axios from "axios";

const TAB_SHUTSUBA = 0;
const TAB_KANRI = 1;


/**
 * けいばページ
 */
const HorseRacePage = () => {
    const [isLoading, setIsLoading] = useState(false);
    const [isLogin, setIsLogin] = useState(false);
    const [userId, setUserId] = useState("");
    const [userPass, setUserPass] = useState("");
    const [selectTab, setSelectTab] = useState(TAB_SHUTSUBA);
    const [adminFlg, setAdminFlg] = useState(false);


    React.useEffect(() => {
        loginCheck();
    }, []);

    /**
     * ログインチェック
     */
    const loginCheck = async() => {
        setIsLoading(true);
        try{
            await axios.post(constants.LOGIN_CHECK_URL,[], { withCredentials: true})
            .then((response) => {
                setIsLogin(response.status == 200);
                setAdminFlg(response.data.admin);
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
            axios.post(constants.LOGIN_URL, {user_id : userId, password : userPass})
            .then( (response) =>{
                if(response.data.token != ""){
                    document.cookie = "chestnut-token=" + response.data.token + "; max-age=3600";
                    setAdminFlg(response.data.admin);
                    setIsLogin(true);
                    setIsLoading(false);
                }
            })
            .catch((error) => {
                console.log(error);
                setIsLoading(false);
            });
        }
        catch(error) {
            console.error(error);
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

    /**
     * タブコンテンツの返却
     */
    const tabContents = () => {
        if(selectTab == TAB_SHUTSUBA) {
            return <ShutsubaContents setLoginMethod={setIsLogin} setLoadingMethod={setIsLoading} />;
        }
        else if(selectTab == TAB_KANRI){
            return <HRManagementContents setLoginMethod={setIsLogin} setLoadingMethod={setIsLoading} />;
        }
    }


    if(isLogin){
        return (
            <div className="horse-race">
                <div className="w-100 p-1">
                    <ul className="nav nav-tabs mb-2">
                        <li className="nav-item">
                            <a className="nav-link active" href="#" data-index={TAB_SHUTSUBA} onClick={(e) => tabClickHandler(e)}>出馬情報</a>
                        </li>
                        {adminFlg ?
                        <li className="nav-item">
                            <a className="nav-link" href="#" data-index={TAB_KANRI} onClick={(e) => tabClickHandler(e)}>管理</a>
                        </li>
                        : ""}
                    </ul>
                    {tabContents()}
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

export default HorseRacePage;

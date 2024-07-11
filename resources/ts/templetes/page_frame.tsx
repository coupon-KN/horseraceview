/**
 * PageFrame
 *   サイトの骨格
 * @param props 
 * @returns 
 */
import { useNavigate } from 'react-router-dom';
import "./page_frame.scss"

const PageFrame = (props:any) => {
    let navigate = useNavigate();

    return (
    <div className='page-frame'>
        <header>
            <b>chestnut</b>
            <div>
                <a className="link-btn" onClick={() => {navigate("/")}}>ホーム</a>
                <a className="link-btn" onClick={() => {navigate("/horserace")}}>けいば</a>
                <a className="link-btn" onClick={() => {navigate("/horsememo")}}>メモ</a>
            </div>
        </header>
        <div className='page-body'>
            {props.children}
        </div>
    </div>
    );
};

export default PageFrame;

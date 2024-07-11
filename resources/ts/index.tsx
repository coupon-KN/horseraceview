/**
 * Appファイル
 *   ルーティングを定義
 */

import { createRoot } from 'react-dom/client';
import { BrowserRouter, Routes, Route } from "react-router-dom";
import PageFrame from "./templetes/page_frame";
import Top from "./pages/top";
import HorseRacePage from "./pages/HorseRacePage";
import HorseMemoPage from "./pages/HorseMemoPage";

const App = () => {
    return (
    <BrowserRouter>
        <PageFrame>
            <Routes>
                <Route path='/' element={<Top />} />
                <Route path='/horserace' element={<HorseRacePage />} />
                <Route path='/horsememo' element={<HorseMemoPage />} />
                <Route path='/*' element={<h1>Not found</h1>} />
            </Routes>
        </PageFrame>
    </BrowserRouter>
    );
};

const container = document.getElementById('reactContents');
const root = createRoot(container!);
root.render(<App />);

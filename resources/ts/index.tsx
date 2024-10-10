/**
 * Appファイル
 *   ルーティングを定義
 */

import { createRoot } from 'react-dom/client';
import { BrowserRouter, Routes, Route } from "react-router-dom";
import PageFrame from "./templetes/page_frame";
import TopPage from "./pages/TopPage";
import HorseRacePage from "./pages/HorseRacePage";


const App = () => {
    return (
    <BrowserRouter>
        <PageFrame>
            <Routes>
                <Route path='/mobile' element={<TopPage />} />
                <Route path='/horserace' element={<HorseRacePage />} />
                <Route path='/*' element={<h1>Not found</h1>} />
            </Routes>
        </PageFrame>
    </BrowserRouter>
    );
};

const container = document.getElementById('reactContents');
const root = createRoot(container!);
root.render(<App />);

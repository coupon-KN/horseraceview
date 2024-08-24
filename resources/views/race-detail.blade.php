@extends('common.base')
@section('pageTitle', $pageTitle)


@section('pageContents')
    @include('common.menu-header', ['title' => $menuTitle])

    @if(!is_null($info))
        <div class="row m-2">
            <div class="col-5">
                <div>{{ $info->raceInfo }}</div>
                <div>{{ $info->courseMemo }}</div>
            </div>
            <div class="col-5 text-end mt-auto mb-0">
                <a href="#" class="btn-tool btn-door" onclick="allOpenClick(this)" title="全て開く"></a>
                @if(session(config("const.SESSION_ADMIN")) == "1")
                        <a href="#" class="btn-tool btn-analysis" onclick="scoringClick()"></a>
                @endif
                <a href="#" class="btn-tool btn-blood blood-off" onclick="bloodClick(this)" title="血統"></a>
            </div>

            <div class="col-10">
                <table class="table-bordered tbl-detail">
                    <thead class="text-center">
                        <th class="py-1" style="width:40px;" >枠</th>
                        <th class="py-1" style="width:40px;" >馬番</th>
                        <th class="py-1" style="width:40px;" >印</th>
                        <th class="py-1" style="width:300px;" >馬名</th>
                        <th class="py-1" style="width:40px;" >性齢</th>
                        <th class="py-1" style="width:40px;" >斤量</th>
                        <th class="py-1" style="width:100px;">騎手</th>
                        <th class="py-1" style="width:100px;">成績</th>
                        <th class="py-1" style="width:60px;" >勝率</th>
                        <th class="py-1" style="width:60px;" >連帯率</th>
                        <th class="py-1" style="width:auto;" >コメント</th>
                    </thead>
                    <tbody>
                    @foreach($info->horseArray as $h)
                        <tr class="normal">
                            <td class="py-2 text-center waku{{$h->waku}}">{{ $h->waku }}</td>
                            <td class="py-2 text-center">{{ $h->umaban }}</td>
                            <td class="text-center fs-4 mark-cell" data-index="{{$loop->index}}" onclick="markCellClick(this)"></td>
                            <td class="py-2 ps-1" onclick="toggleRow(this)">{{ $h->name }}</td>
                            <td class="py-2 text-center" onclick="toggleRow(this)">{{ $h->age }}</td>
                            <td class="py-2 text-center" onclick="toggleRow(this)">{{ $h->kinryo }}</td>
                            <td class="py-2 ps-1" onclick="toggleRow(this)">{{ $h->jockey }}</td>
                            <td class="py-2 text-center" onclick="toggleRow(this)">{{ $h->recode }}</td>
                            <td class="py-2 text-center" onclick="toggleRow(this)">{{ $h->winRate }}%</td>
                            <td class="py-2 text-center" onclick="toggleRow(this)">{{ $h->podiumRate }}%</td>
                            <td class="px-2">
                                <input type="text" class="w-100 border-0 comment-cell" data-index="{{$loop->index}}" onChange="commentChange(this)">
                            </td>
                        </tr>
                        <tr class="row-hidden">
                            <td colspan="11" class="p-1" style="background-color:#dee2e6;">
                                @include('horse-history-table', ['horse' => $h])
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-2">
                <pre id="score" class="w-100 h-100 m-0"></pre>
            </div>

        </div>
    @else
        <div class="p-4 fs-4">
            <span>対象のデータが存在しません</span>
        </div>
    @endif

    <input type="hidden" id="raceId" value="{{$raceid}}" />
    <input type="hidden" id="urlScoringRace" value="{{route('api.race.scoring', $raceid)}}" />
@endsection


@section('pageJs')
<script type="text/javascript">
    window.onload = function() {
        let raceId = document.getElementById("raceId").value;

        const markCells = document.getElementsByClassName("mark-cell");
        for(let i=0; i<markCells.length; i++) {
            let val = getMarkCookie("mark" + raceId + markCells[i].dataset.index);
            if(val.length > 0){
                markCells[i].innerText = val;
                const parentRow = markCells[i].parentElement;
                if(val == "消"){
                    parentRow.className = parentRow.className.replace("normal", "delete");
                }
            }
        }

        const commentCells = document.getElementsByClassName("comment-cell");
        for(let i=0; i<commentCells.length; i++) {
            let val = getMarkCookie("com" + raceId + commentCells[i].dataset.index);
            if(val.length > 0){
                commentCells[i].value = val;
            }
        }

    }

    function toggleRow(e){
        const targetRow = e.parentElement.nextElementSibling;
        if(targetRow.className.indexOf('row-hidden') >= 0){
            targetRow.className = targetRow.className.replace("row-hidden", "row-visible");
        }else{
            targetRow.className = targetRow.className.replace("row-visible", "row-hidden");
        }
    }

    function markCellClick(cell) {
        const markArr = ["", "◎", "〇", "▲", "△", "☆", "消"];
        let currentMark = cell.innerText;
        cell.innerText = "";
        for(let i=0; i<markArr.length-1; i++) {
            if(currentMark == markArr[i]){
                cell.innerText = markArr[i+1];
                break;
            }
        }
        
        // マーク保存
        let raceId = document.getElementById("raceId").value;
        saveMarkCookie("mark" + raceId + cell.dataset.index, cell.innerText);

        // 背景制御
        const parentRow = cell.parentElement;
        if(cell.innerText == "消"){
            parentRow.className = parentRow.className.replace("normal", "delete");
        }else{
            parentRow.className = parentRow.className.replace("delete", "normal");
        }
    }

    function commentChange(comment) {
        // コメント保存
        let raceId = document.getElementById("raceId").value;
        saveMarkCookie("com" + raceId + comment.dataset.index, comment.value);
    }

    function getMarkCookie(cookieName) {
        let cookies = document.cookie;
        let rtnContent = "";
        if(cookies != ""){
            cookies.split(';').forEach(function(value) {
                let keyVal = value.trim().split('=');
                if(keyVal[0] == cookieName){
                    rtnContent = keyVal[1];
                    return;
                }
            });
        }
        return rtnContent;
    }

    function saveMarkCookie(name, value) {
        document.cookie = name + "=" + value + "; max-age=259200";
    }

    function bloodClick(e) {
        const tblParents = document.getElementsByClassName("tbl-horse-parent");
        let isOn = (e.className.indexOf('blood-off') >= 0);
        if(isOn){
            e.className = e.className.replace("blood-off", "blood-on");
        }else{
            e.className = e.className.replace("blood-on", "blood-off");
        }
        for(let i=0; i<tblParents.length; i++) {
            tblParents[i].style = isOn ? "display : block;" : "display : none;";
        }
    }

    function allOpenClick(e) {
        const rows = document.getElementsByClassName("row-hidden");
        for(let i=rows.length-1; i>=0; i--) {
            rows[i].className = rows[i].className.replace("row-hidden", "row-visible");
        }
    }

    async function scoringClick() {
        const url = document.getElementById("urlScoringRace").value;
        try {
            const response = await fetch(url, {method:"POST"});
            if(response.ok){
                const score = document.getElementById("score");
                const json = await response.json();
                console.log(json);

                score.innerText = "";
                let strScore = "";
                for(let i=0; i<json.length; i++) {
                    strScore += json[i].umaban + "番 " + json[i].name;
                    strScore += "\n";
                    strScore += "　" + json[i].class + "級";
                    strScore += "\n";
                    strScore += "　勝ち馬場等級：" + json[i].babaRank.rank1 + " - " + json[i].babaRank.rank2 + " - " + json[i].babaRank.rank3;
                    strScore += "\n";
                    strScore += "　距離適性率：" + json[i].distance;
                    strScore += "\n";
                }
                score.innerText = strScore;
            }
        } catch (error) {
            console.log(error);
        }
    }

</script>
@endsection


@section('pageCss')
<style>
    .tbl-detail {
        width: 100%;
        border-color: #dee2e6;
    }
    .tbl-detail > thead {
        background-color: #f4f2ec;
        font-size: small;
    }
    .tbl-detail tr.row-hidden {
        display: none;
    }
    .tbl-detail tr.delete,
    .tbl-detail tr.delete input {
        background-color: #d0d0d0;
    }
    a.btn-tool {
        display: inline-block;
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
    }
    a.btn-blood {
        width: 64px;
        height: 29px;
    }
    a.blood-off {
        background-image: url(/img/bloodline-off.png);
    }
    a.blood-on {
        background-image: url(/img/bloodline-on.png);
    }
    a.btn-analysis {
        width: 29px;
        height: 29px;
        margin-right: 15px;
        background-image: url(/img/analysis.png);
    }
    a.btn-door {
        width: 29px;
        height: 29px;
        margin-right: 15px;
        background-image: url(/img/door.png);
    }


</style>
@endsection

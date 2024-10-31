@extends('common.base')
@section('pageTitle', $pageTitle)


@section('pageContents')
    @include('common.menu-header', ['title' => $menuTitle])

    @if(!is_null($info))
        <div class="row m-2">
            <div class="col-6">
                <div>{{ $info->raceInfo . " " . mb_convert_kana($info->raceGarade, "n") }}</div>
                <div>{{ $info->courseMemo }}</div>
            </div>
            <div class="col-5 text-end mt-auto mb-0">
                <a href="#" class="btn-tool btn-door" onclick="allOpenClick(this)" title="全て開く"></a>
                <a href="#" class="btn-tool btn-blood blood-off" onclick="bloodClick(this)" title="血統"></a>
            </div>

            <div class="col-11">
                <table class="table-bordered tbl-detail">
                    <thead class="text-center">
                        <th class="py-1" style="width:40px;" >枠</th>
                        <th class="py-1" style="width:40px;" >馬番</th>
                        <th class="py-1" style="width:40px;" >印</th>
                        <th class="py-1" style="width:300px;">馬名</th>
                        <th class="py-1" style="width:40px;" >性齢</th>
                        <th class="py-1" style="width:40px;" >斤量</th>
                        <th class="py-1" style="width:100px;">騎手</th>
                        <th class="py-1" style="width:100px;">成績</th>
                        <th class="py-1" style="width:60px;" >勝率</th>
                        <th class="py-1" style="width:60px;" >連帯率</th>
                        <th class="py-1" style="width:100px;" >獲得賞金</th>
                        <th class="py-1" style="width:70px;" >
                            @if($centralFlg)
                            <a href="#" onclick="scoringClick()">独自採点</a>
                            @else
                            工事中
                            @endif
                        </th>
                        <th class="py-1" style="width:auto;" >コメント</th>
                    </thead>
                    <tbody>
                    @foreach($info->horseArray as $h)
                        @if($h->isCancel)
                            <tr class="deselect">
                                <td class="py-2 text-center waku{{$h->waku}}">{{ $h->waku }}</td>
                                <td class="py-2 text-center">{{ $h->umaban }}</td>
                                <td class="text-center fs14" data-index="{{$loop->index}}">除外</td>
                                <td class="py-2 ps-1">{{ $h->name }}</td>
                                <td class="py-2 text-center">{{ $h->age }}</td>
                                <td class="py-2 text-center">{{ $h->kinryo }}</td>
                                <td class="py-2 ps-1">{{ $h->jockey }}</td>
                                <td class="py-2 text-center">{{ $h->recode }}</td>
                                <td class="py-2 text-center">{{ $h->winRate }}%</td>
                                <td class="py-2 text-center">{{ $h->podiumRate }}%</td>
                                <td class="py-2 text-center fs12">{!! nl2br($h->totalPrize) !!}</td>
                                <td class="py-2 text-center score{{$h->horseId}}"></td>
                                <td class="px-2">
                                    <input type="text" class="w-100 border-0 comment-cell" data-horse-id="{{$h->horseId}}" onChange="commentChange(this)" value="{{$h->userComment}}">
                                </td>
                            </tr>
                        @else
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
                                <td class="py-2 text-center fs12" onclick="toggleRow(this)">{!! nl2br($h->totalPrize) !!}</td>
                                <td class="py-2 text-center score{{$h->horseId}}" onclick="toggleRow(this)"></td>
                                <td class="px-2">
                                    <input type="text" class="w-100 border-0 comment-cell" data-horse-id="{{$h->horseId}}" onChange="commentChange(this)" value="{{$h->userComment}}">
                                </td>
                            </tr>
                            <tr class="row-hidden">
                                <td colspan="13" class="p-1" style="background-color:#dee2e6;">
                                    @include('horse-history-table', ['horse' => $h])
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-1">
            </div>

        </div>
    @else
        <div class="p-4 fs-4">
            <span>対象のデータが存在しません</span>
        </div>
    @endif

    <input type="hidden" id="raceId" value="{{$raceid}}" />
    <input type="hidden" id="urlScoringRace" value="{{route('detail.scoring', $raceid)}}" />
    <input type="hidden" id="urlComment" value="{{route('detail.comment')}}" />
@endsection


@section('pageJs')
<script type="text/javascript">
    const COOKIE_AGE = String(60 * 60 * 24 * 3);

    window.onload = function() {
        let raceId = document.getElementById("raceId").value;

        const markCells = document.getElementsByClassName("mark-cell");
        for(let i=0; i<markCells.length; i++) {
            markCells[i].innerText = getCookie("mark" + raceId + markCells[i].dataset.index);
            if(markCells[i].innerText == "消"){
                let parentRow = markCells[i].parentElement;
                parentRow.className = parentRow.className.replace("normal", "delete");
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
        setCookie("mark" + raceId + cell.dataset.index, cell.innerText, COOKIE_AGE);

        // 背景制御
        const parentRow = cell.parentElement;
        if(cell.innerText == "消"){
            parentRow.className = parentRow.className.replace("normal", "delete");
        }else{
            parentRow.className = parentRow.className.replace("delete", "normal");
        }
    }

    function commentChange(comment) {
        const url = document.getElementById("urlComment").value;
        const addHeader = {'Content-Type': 'application/json'};
        const bodyData = JSON.stringify({
            race_id : document.getElementById("raceId").value,
            horse_id : comment.dataset.horseId,
            comment : comment.value
        });
        asyncPost(url, addHeader, bodyData, () => {});
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
        try {
            const url = document.getElementById("urlScoringRace").value;
            const addHeader = {'Content-Type': 'application/json'};
            asyncPost(url, addHeader, null, async(response) => {
                const json = await response.json();
                console.log(json);

                let strScore = "";
                for(let i=0; i<json.length; i++) {
                    let td = document.getElementsByClassName("score" + json[i].id);
                    td[0].innerText = json[i].score + "点";
                }
            });
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
    .tbl-detail tr.deselect,
    .tbl-detail tr.deselect input {
        background-color: #a9a9a9;
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
    a.btn-door {
        width: 29px;
        height: 29px;
        margin-right: 15px;
        background-image: url(/img/door.png);
    }

</style>
@endsection

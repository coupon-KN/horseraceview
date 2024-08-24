@extends('common.base')
@section('pageTitle', "レースカレンダー")


@section('pageContents')
    @include('common.menu-header', ['title' => 'レースカレンダー'])

    <div class="row m-2">
        <div class="col-12">
            <a class="btn btn-link" href="#" onclick="changeMonth(this); return false;" data-sel-ym="{{ date('Ym', strtotime($targetDate . ' -1 Month')) }}">前の月</a>
            <span class="fs-5">{{ date("Y年m月", strtotime($targetDate)) }}</span>
            <a class="btn btn-link" href="#" onclick="changeMonth(this); return false;" data-sel-ym="{{ date('Ym', strtotime($targetDate . ' +1 Month')) }}">次の月</a>

            {{ Form::open(['route' => 'calendar.index', 'method' => 'get', 'id' => 'frmChangeMonth']) }}
                <input type="hidden" id="selYm" name="selYm" />
            {{ Form::close() }}
        </div>

        <div class="col-6">
            <table class="table table-bordered calendar">
                <thead class="text-center">
                    <th>日</th>
                    <th>月</th>
                    <th>火</th>
                    <th>水</th>
                    <th>木</th>
                    <th>金</th>
                    <th>土</th>
                </thead>
                @foreach($calendar as $week)
                <tr>
                    @foreach($week as $day)
                        @if(empty($day))
                            <td></td>
                        @else
                            <td @if($day==date('Y-m-d')) class="today" @endif>
                                <span>{{ date("j", strtotime($day)) }}</span>
                                <a href="#" class="float-end" style="font-size: 0.7em;" onclick="kickScrapingSchedule(this); return false;" data-target-date="{{$day}}" title="開催情報取得">
                                    <img src="/img/download.png" alt="取得" />
                                </a>
                                <div>
                                @if(array_key_exists($day, $kaisaiData))
                                    @if(count($kaisaiData[$day]["central"]) > 0)
                                    <div style="font-size:10px;">中央</div>
                                    <div style="font-size:14px;">
                                        @foreach($kaisaiData[$day]["central"] as $item)
                                            <a href="#" onclick="getRaceInfo(this); return false;" data-target-date="{{$day}}" data-race-id="{{ $item['id'] }}">{{ $item["name"] }}</a>
                                        @endforeach
                                    </div>
                                    @endif
                                    @if(count($kaisaiData[$day]["region"]) > 0)
                                    <div style="font-size:10px;">地方</div>
                                    <div style="font-size:14px;">
                                        @foreach($kaisaiData[$day]["region"] as $item)
                                            <a href="#" onclick="getRaceInfo(this); return false;" data-target-date="{{$day}}" data-race-id="{{ $item['id'] }}">{{ $item["name"] }}</a>
                                        @endforeach
                                    </div>
                                    @endif
                                @endif
                                </div>
                            </td>
                        @endif
                    @endforeach
                </tr>
                @endforeach
            </table>
        </div>

        <div class="col-6">
            <div id="raceDetail" style="display: none;"></div>
        </div>
    </div>
    <input type="hidden" id="urlGetRaceInfo" value="{{ route('calendar.raceinfo') }}" />
    <input type="hidden" id="urlRaceDetail" value="{{ route('detail.index', 'dummy') }}" />
    <input type="hidden" id="urlKickRaceData" value="{{ route('calendar.kick.racedata') }}" />
    <img src="/img/spinner.gif" style="display:none" />
    {{ Form::open(['route' => 'calendar.get.schedule', 'method' => 'post', 'id' => 'frmScrapingSchedule']) }}
        <input type="hidden" id="selDate" name="sel_date" />
    {{ Form::close() }}

@endsection


@section('pageJs')
<script type="text/javascript">
    function changeMonth(e) {
        document.getElementById("selYm").value = e.dataset.selYm;
        document.getElementById("frmChangeMonth").submit();
    }

    function kickScrapingSchedule(e) {
        document.getElementById("selDate").value = e.dataset.targetDate;
        document.getElementById("frmScrapingSchedule").submit();
    }

    function getHeaders(){
        return {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        };
    }

    async function getRaceInfo(e) {
        const url = document.getElementById("urlGetRaceInfo").value;
        const reqData = {sel_date : e.dataset.targetDate, race_id : e.dataset.raceId};
        try {
            const response = await fetch(url, {method:"POST", headers:getHeaders(), body:JSON.stringify(reqData)});
            if(response.ok){
                const json = await response.json();
                const detailBlock = document.getElementById("raceDetail");
                detailBlock.innerHTML = json.view;
                detailBlock.style = "display : block;";

                // ボタンを登録
                const btnRaceArr = document.getElementsByClassName("race-scraping");
                for(let i=0; i<btnRaceArr.length; i++) {
                    btnRaceArr[i].onclick = kickScrapingRaceDara;
                }
                const btnBulkArr = document.getElementsByClassName("bulk-scraping");
                for(let i=0; i<btnBulkArr.length; i++) {
                    btnBulkArr[i].onclick = bulkScrapingRaceDara;
                }

            }
        } catch (error) {
            console.log(error);
        }
    }

    async function kickScrapingRaceDara(e){
        const tr = e.currentTarget.parentElement.parentElement;
        const div = document.createElement("div");
        div.className = "waiting";
        tr.getElementsByTagName("td")[4].innerText = "";
        tr.getElementsByTagName("td")[4].append(div);
        await callScrapingRaceData(e.currentTarget.dataset.raceId);
    }
    async function bulkScrapingRaceDara(e){
        const btnRaceArr = document.getElementsByClassName("race-scraping");
        for(let i=0; i<btnRaceArr.length; i++) {
            const tr = document.getElementById("raceDetail").getElementsByTagName("tr")[i];
            const div = document.createElement("div");
            div.className = "waiting";
            tr.getElementsByTagName("td")[4].innerText = "";
            tr.getElementsByTagName("td")[4].append(div);
            await callScrapingRaceData(btnRaceArr[i].dataset.raceId);
        }
    }

    async function callScrapingRaceData(raceId) {
        const url = document.getElementById("urlKickRaceData").value;
        const reqData = {race_id : raceId};
        try {
            const response = await fetch(url, {method:"POST", headers:getHeaders(), body:JSON.stringify(reqData)});
            if(response.ok){
                const json = await response.json();
                console.log(json);

                const trArr = document.getElementById("raceDetail").getElementsByTagName("tr");
                const tr = trArr[json.data.raceNo - 1];

                let anchor = document.createElement("a");
                tr.getElementsByTagName("td")[1].innerText = json.data.startingTime;
                tr.getElementsByTagName("td")[2].innerText = json.data.distance;
                tr.getElementsByTagName("td")[3].innerText = json.data.horseCount;
                tr.getElementsByTagName("td")[4].innerText = "";
                anchor.href = json.data.detailUrl;
                anchor.target = "_blank";
                anchor.text = json.data.name;
                tr.getElementsByTagName("td")[4].append(anchor);
            }
        } catch (error) {
            console.log(error);
        }
    }


</script>
@endsection


@section('pageCss')
<style>
    table {
        table-layout:fixed;
    }
    .calendar {
        margin-bottom:0px !important;
    }
    .calendar th {
        background-color:#fff4e6 !important;
    }
    .calendar td {
        height: 130px;
        position: relative;
    }
    .calendar td:nth-child(1) {
        background-color:#fbf1f1 !important;
    }
    .calendar td:nth-child(7) {
        background-color:#f1f4fb !important;
    }
    .calendar td.today {
        background-color:#fffada !important;
    }
    .calendar td > div {
        position: absolute;
        bottom: 0;
        margin-bottom: 4px;
    }
    #raceDetail {
        position: relative;
        border: dashed 1px #ccc;
        height: 100%;
        padding: 5px 10px;
    }
    table.detail td:nth-child(1) {width : 40px;}
    table.detail td:nth-child(2) {width : 60px;}
    table.detail td:nth-child(3) {width : 90px;}
    table.detail td:nth-child(4) {width : 60px;}
    table.detail td:nth-child(5) {width : auto;}
    table.detail td:nth-child(6) {width : 80px;}
    .waiting{
        width: 24px;
        height: 24px;
        background-image: url("/img/spinner.gif");
        background-position: center;
        background-repeat: no-repeat;
    }
</style>
@endsection

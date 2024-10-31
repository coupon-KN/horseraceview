<table class="tbl-horse-parent" style="display:none;">
    <tr>
        <td rowspan="2" class="p-1 bg-dad">
            <div class="d-inline-block" style="width:150px;">{{$horse->dad->name}}</div>
            <div class="d-inline-block" style="width:170px;">{{$horse->dad->recode . " 勝" . $horse->dad->winRate . "% 連" . $horse->dad->podiumRate . "%"}}</div>
        </td>
        <td class="p-1 bg-dad">
            <div class="d-inline-block" style="width:150px;">{{$horse->dadSohu->name}}</div>
            <div class="d-inline-block" style="width:170px;">{{$horse->dadSohu->recode . " 勝" . $horse->dadSohu->winRate . "% 連" . $horse->dadSohu->podiumRate . "%"}}</div>
        </td>
    </tr>
    <tr>
        <td class="p-1 bg-mam">
            <div class="d-inline-block" style="width:150px;">{{$horse->dadSobo->name}}</div>
            <div class="d-inline-block" style="width:170px;">{{$horse->dadSobo->recode . " 勝" . $horse->dadSobo->winRate . "% 連" . $horse->dadSobo->podiumRate . "%"}}</div>
        </td>
    </tr>
    <tr>
        <td rowspan="2" class="p-1 bg-mam">
            <div class="d-inline-block" style="width:150px;">{{$horse->mam->name}}</div>
            <div class="d-inline-block" style="width:170px;">{{$horse->mam->recode . " 勝" . $horse->mam->winRate . "% 連" . $horse->mam->podiumRate . "%"}}</div>
        </td>
        <td class="p-1 bg-dad">
            <div class="d-inline-block" style="width:150px;">{{$horse->mamSohu->name}}</div>
            <div class="d-inline-block" style="width:170px;">{{$horse->mamSohu->recode . " 勝" . $horse->mamSohu->winRate . "% 連" . $horse->mamSohu->podiumRate . "%"}}</div>
        </td>
    </tr>
    <tr>
        <td class="p-1 bg-mam">
            <div class="d-inline-block" style="width:150px;">{{$horse->mamSobo->name}}</div>
            <div class="d-inline-block" style="width:170px;">{{$horse->mamSobo->recode . " 勝" . $horse->mamSobo->winRate . "% 連" . $horse->mamSobo->podiumRate . "%"}}</div>
        </td>
    </tr>
</table>
@if(count($horse->recodeArray) == 0)
    <span class="tbl-history">競走成績がありません</span>
@else
    <table class="table-bordered tbl-history">
        <thead class="text-center">
            <tr>
                <th width="85px">日付</th>
                <th width="60px">馬場</th>
                <th width="40px">天気</th>
                <th width="35px">No</th>
                <th width="200px">レース名</th>
                <th width="50px">格</th>
                <th width="35px">頭数</th>
                <th width="35px">枠番</th>
                <th width="35px">馬番</th>
                <th width="35px">着順</th>
                <th width="100px">騎手</th>
                <th width="35px">斤量</th>
                <th width="60px">距離</th>
                <th width="35px">状態</th>
                <th width="60px">タイム</th>
                <th width="40px">着差</th>
                <th width="90px">通過</th>
                <th width="80px">ﾍﾟｰｽ</th>
                <th width="60px">上り</th>
                <th width="35px">人気</th>
                <th width="40px">オッズ</th>
                <th width="70px">馬体重</th>
                <th width="200px">勝ち馬</th>
            </tr>
        </thead>
        <tbody style="background-color: white;">
            @foreach($horse->recodeArray as $rec)
            <tr>
                <td class="text-center">{{ $rec->date }}</td>
                <td class="text-center ps-1">{{ $rec->baba }}</td>
                <td class="text-center">{{ $rec->tenki }}</td>
                <td class="text-center">{{ $rec->raceNo }}</td>
                <td class="ps-1">
                    @if(empty($rec->raceUrl))
                        <span>{{ $rec->raceName }}</span>
                    @else
                        <a href="{{$rec->raceUrl}}" target="_blank">{{ $rec->raceName }}</a>
                    @endif
                </td>
                <td class="text-center">{{ $rec->raceGrade }}</td>
                <td class="text-center">{{ $rec->horseCount }}</td>
                <td class="text-center">{{ $rec->waku }}</td>
                <td class="text-center">{{ $rec->umaban }}</td>
                <td class="text-center rank{{$rec->rankNo}}">{{ $rec->rankNo }}</td>
                <td class="ps-1">{{ $rec->jockey }}</td>
                <td class="text-center">{{ $rec->kinryo }}</td>
                <td class="text-center">{{ config("const.GROUND_SHORT_NAME")[$rec->groundType] . $rec->distance }}</td>
                <td class="text-center">{{ $rec->condition }}</td>
                <td class="text-center">{{ $rec->time }}</td>
                <td class="text-center">{{ $rec->difference }}</td>
                <td class="text-center">{{ $rec->pointTime }}</td>
                <td class="text-center">{{ $rec->firstPace . " - " . $rec->latterPace }}</td>
                <td class="text-center">{{ $rec->agari600m }}</td>
                <td class="text-center">{{ $rec->ninki }}</td>
                <td class="text-center">{{ $rec->odds }}</td>
                <td class="text-center">{{ $rec->weight }}</td>
                <td class="ps-1">{{ $rec->winHorse }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif

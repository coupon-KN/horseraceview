@if(is_null($recodeArray))
    データがありません
@else
    <table class="table-bordered" style="font-size: 0.85em;">
        <thead class="text-center">
            <tr style="background-color:#f0f0f0;">
                <th width="80px">日付</th>
                <th width="60px">馬場</th>
                <th width="35px">天気</th>
                <th width="35px">No</th>
                <th width="200px">レース名</th>
                <th width="35px">頭数</th>
                <th width="35px">枠番</th>
                <th width="35px">馬番</th>
                <th width="50px">オッズ</th>
                <th width="35px">人気</th>
                <th width="35px">着順</th>
                <th width="100px">騎手</th>
                <th width="35px">斤量</th>
                <th width="70px">距離</th>
                <th width="35px">状態</th>
                <th width="70px">タイム</th>
                <th width="40px">着差</th>
                <th width="80px">通過</th>
                <th width="50px">ﾍﾟｰｽ前</th>
                <th width="50px">ﾍﾟｰｽ後</th>
                <th width="60px">ｱｶﾞﾘ3</th>
                <th width="70px">馬体重</th>
                <th width="200px">勝ち馬</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recodeArray as $rec)
            <tr>
                <td class="text-center">{{ $rec->date }}</td>
                <td class="ps-1">{{ $rec->baba }}</td>
                <td class="text-center">{{ $rec->tenki }}</td>
                <td class="text-center">{{ $rec->raceNo }}</td>
                <td class="ps-1">{{ $rec->raceName }}</td>
                <td class="text-center">{{ $rec->horseCount }}</td>
                <td class="text-center">{{ $rec->waku }}</td>
                <td class="text-center">{{ $rec->umaban }}</td>
                <td class="text-center">{{ $rec->odds }}</td>
                <td class="text-center">{{ $rec->ninki }}</td>
                <td class="text-center rank{{$rec->rankNo}}">{{ $rec->rankNo }}</td>
                <td class="ps-1">{{ $rec->jockey }}</td>
                <td class="text-center">{{ $rec->kinryo }}</td>
                <td class="text-center">{{ config("const.GROUND_SHORT_NAME")[$rec->groundType] . $rec->distance }}</td>
                <td class="text-center">{{ $rec->condition }}</td>
                <td class="text-center">{{ $rec->time }}</td>
                <td class="text-center">{{ $rec->difference }}</td>
                <td class="text-center">{{ $rec->pointTime }}</td>
                <td class="text-center">{{ $rec->firstPace }}</td>
                <td class="text-center">{{ $rec->latterPace }}</td>
                <td class="text-center">{{ $rec->agari600m }}</td>
                <td class="text-center">{{ $rec->weight }}</td>
                <td class="ps-1">{{ $rec->winHorse }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif

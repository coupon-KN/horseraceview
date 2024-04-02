@extends('common.base')
@section('pageTitle', '競走馬情報取得')


@section('pageContents')
    <header style="padding:10px; background:#888; color:#fff;">
        設定 - 競走馬情報取得
    </header>

    @if(!is_null($race_obj))
        <div class="race-info">
            <div>{{ $race_obj->name }}</div>
            <div>
                <span>{{ ["","芝","ダ","障"][$race_obj->groundType] }}</span>
                <span>{{ $race_obj->distance }}</span>
                <span>{{ ["","左","右"][$race_obj->direction] }}</span>
                <span>{{ $race_obj->horseCount }}頭</span>
            </div>
            <table>
                <thead>
                    <th>枠番</th>
                    <th>馬番</th>
                    <th>名前</th>
                    <th>年齢</th>
                    <th>状況</th>
                    <th>取得</th>
                </thead>
                <tbody>
                    @foreach($race_obj->shutsubaArray as $item)
                    <tr>
                        <td>{{ $item->waku }}</td>
                        <td>{{ $item->umaban }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->age }}</td>
                        <td>@if($put_arr[$loop->index]) <font color="green">済</font> @else <font color="red">未</font>@endif</td>
                        <td>
                            <a href="#" onclick="submitData(this);" data-horse-id="{{$item->horseId}}">取得</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="display:none;">
                {{ Form::open(['route' => 'setting.horseload.getHorseData', 'method' => 'post', 'id' => 'frmPost']) }}
                    <input type="hidden" name="horse_id" id="queryHorseId">
                    <input type="hidden" name="race_id" value="{{$race_obj->raceId}}">
                {{ Form::close() }}
            </div>
        </div>
    @else
        <span>データなし</span>
    @endif
@endsection


@section('pageCss')
<style>
    table {
        border-collapse: collapse;
    }
    th,td {
        border: solid 1px #888;
        padding: 4px;
    }
    th {
        background: #eee;
    }
    td a {
        color: blue;
    }
    .race-info {
        padding:10px 40px;
    }
</style>
@endsection


@section('pageJs')
<script type="text/javascript">
    function submitData(element){
        document.getElementById("queryHorseId").value = element.dataset.horseId;
        document.getElementById("frmPost").submit();
    }
</script>
@endsection

@extends('common.base')
@section('pageTitle', 'レース情報取得')


@section('pageContents')
    <header style="padding:10px; background:#888; color:#fff;">
        設定 - レース情報取得
    </header>
    <div class="conditions">
        {{-- 検索日 --}}
        {{ Form::open(['route' => 'setting.raceload', 'method' => 'get']) }}
            <div style="padding-bottom: 4px;">
                <span class="item-name">検索日時</span>
                <input type="date" name="sel_date" value="{{ $sel_date }}">
                <button type="submit">決定</button>
            </div>
            <div>
                <span class="item-name">レース</span>
                <select name="sel_sche">
                    @foreach($schedule_list as $val)
                    <option value="{{ $val->id }}"
                        @if($val->id == $sel_sche) selected @endif
                    >{{$val->name}}</option>
                    @endforeach
                </select>
                <button type="submit">決定</button>
            </div>
        {{ Form::close() }}
    </div>
    @if(count($race_list) > 0)
    <div class="race-list">
        <table>
            <thead>
                <th>No</th>
                <th>名称</th>
                <th>状況</th>
                <th>レース情報</th>
                <th>競走馬情報</th>
                <th>レース参照</th>
            </thead>
            <tbody>
                @foreach($race_list as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <span>{{ $item["name"] }}</span>
                    </td>
                    <td>@if($item["put_flg"]) <font color="green">済</font> @else <font color="red">未</font>@endif</td>
                    <td>
                        <a href="#" onclick="submitData(this);" data-race-id="{{$item['id']}}">取得</a>
                    </td>
                    <td>
                        @if($item["put_flg"])
                        <a href="{{ route('setting.horseload',['race_id' => $item['id']]) }}">取得</a>
                        @endif
                    </td>
                    <td>
                        @if($item["put_flg"])
                        {{-- <a href="{{ route('raceview',['race_id' => $item['id']]) }}">ページへ</a> --}}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ Form::open(['route' => 'setting.raceload.getBulkRaceData', 'method' => 'post']) }}
            @foreach($race_list as $item)
                <input type="hidden" name="race_id[]" value="{{$item['id']}}">
            @endforeach
            <input type="hidden" name="sel_date" value="{{$sel_date}}">
            <input type="hidden" name="sel_sche" value="{{$sel_sche}}">
            <button type="submit">一括取得</button>
        {{ Form::close() }}

        <div style="display:none;">
            {{ Form::open(['route' => 'setting.raceload.getRaceData', 'method' => 'post', 'id' => 'frmPost']) }}
                <input type="hidden" name="race_id" id="queryRaceId">
                <input type="hidden" name="sel_date" value="{{$sel_date}}">
                <input type="hidden" name="sel_sche" value="{{$sel_sche}}">
            {{ Form::close() }}
        </div>
    </div>
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
    .item-name {
        display: inline-block;
        width: 80px;
    }
    .conditions {
        padding:10px;
    }
    .conditions input,
    .conditions select {
        font-size: 14px;
        padding: 2px;
    }
    .race-list {
        padding:10px 40px;
    }
</style>
@endsection


@section('pageJs')
<script type="text/javascript">
    function submitData(element){
        document.getElementById("queryRaceId").value = element.dataset.raceId;
        document.getElementById("frmPost").submit();
    }
</script>
@endsection

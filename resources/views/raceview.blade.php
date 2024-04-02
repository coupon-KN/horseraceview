@extends('common.base')
@section('pageTitle', is_null($race) ? "データがありません" : $race->raceName)


@section('pageContents')
@if(is_null($race))
    <h1>データがありません</h1>
@else
    <header class="px-3 py-1">
        <font size="5">{{ $race->raceName }}</font>
        <span>({{ $race->kaisai }})</span>
        <span>{{ $race->raceInfo }}</span>
    </header>
    <div class="px-3">
        <table class="w-100 table-bordered">
            <thead class="text-center" style="background-color: #f4f2ec">
                <tr>
                    <th width="35px">枠</th>
                    <th width="35px">馬番</th>
                    <th width="35px">印</th>
                    <th width="200px">馬名</th>
                    <th width="40px">性齢</th>
                    <th width="90px">騎手</th>
                    <th width="90px">成績</th>
                    <th width="60px">勝率</th>
                    <th width="60px">複勝率</th>
                    <th width="auto"></th>
                </tr>
            </thead>
            <tbody>
            @foreach($race->horseArray as $h)
                <tr class="his-trigger">
                    <td class="text-center waku{{$h->waku}}">{{ $h->waku }}</td>
                    <td class="text-center">{{ $h->umaban }}</td>
                    <td><input type="text" style="width:30px"></td>
                    <td class="ps-1">{{ $h->name }}</td>
                    <td class="text-center">{{ $h->age }}</td>
                    <td class="ps-1">{{ $h->jockey }}</td>
                    <td class="text-center">{{ $h->recode }}</td>
                    <td class="text-center">{{ $h->winRate }}%</td>
                    <td class="text-center">{{ $h->podiumRate }}%</td>
                    <td></td>
                </tr>
                <tr class="passive">
                    <td colspan="10" class="p-2">
                        @include('horse-history-table', ['recodeArray' => $h->recodeArray])
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection


@section('pageJs')
<script type="text/javascript">
    window.onload = function() {
        let targets = document.getElementsByClassName("his-trigger");
        for(let i = 0; i < targets.length; i++){
            targets[i].addEventListener("click",(event) => {
                let nextEle = event.currentTarget.nextElementSibling;
                if(nextEle != null){
                    if(nextEle.checkVisibility()){
                        nextEle.className = nextEle.className.replace("active", "passive");
                    }else{
                        nextEle.className = nextEle.className.replace("passive", "active");
                    }
                }
            }, false);
        }
    }
</script>
@endsection


@section('pageCss')
<style>
    .passive {
        display: none;
    }
</style>
@endsection

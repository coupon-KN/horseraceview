@extends('common.base')
@section('pageTitle', $pageTitle)


@section('pageContents')
    @include('common.menu-header', ['title' => $menuTitle])

    @if(!is_null($info))
        <div class="row m-2">
            <div class="col-12">
                <div>{{ $info->raceInfo }}</div>
                <div>{{ $info->courseMemo }}</div>
            </div>

            <div class="col-40">
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
                            <th width="60px">連帯率</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($info->horseArray as $h)
                        <tr class="his-trigger">
                            <td class="text-center waku{{$h->waku}}">{{ $h->waku }}</td>
                            <td class="text-center">{{ $h->umaban }}</td>
                            <td></td>
                            <td class="ps-1">{{ $h->name }}</td>
                            <td class="text-center">{{ $h->age }}</td>
                            <td class="ps-1">{{ $h->jockey }}</td>
                            <td class="text-center">{{ $h->recode }}</td>
                            <td class="text-center">{{ $h->winRate }}%</td>
                            <td class="text-center">{{ $h->podiumRate }}%</td>
                        </tr>
                        {{-- 
                        <tr class="passive">
                            <td colspan="10" class="p-2">
                                @include('horse-history-table', ['recodeArray' => $h->recodeArray])
                            </td>
                        </tr>
                        --}}
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="col-30">
                <div class="container">
                @foreach($info->horseArray as $horse)
                    <div>{{ $horse->name }}</div>
                    @foreach($horse->recodeArray as $his)
                        <div class="row m-0 border fs12 {{ 'rank' . $his->rankNo}}">
                            <div class="col-4 p-1">
                                <div>{{$his->date}}</div>
                                <div>{{$his->baba}}</div>
                                <div>{{$his->raceName}}</div>
                                <div>{{$his->groundShortName . $his->distance . " " . $his->condition}}</div>
                            </div>
                            <div class="col-1 p-1 fs-4">
                                <div>{{$his->rankNo}}</div>
                            </div>
                            <div class="col-7 p-1">
                                <div style="letter-spacing: -1em;">
                                    <span class='w-50 d-inline-block'>{{$his->jockey}}</span>
                                    <span class='w-25 d-inline-block'>{{$his->kinryo}}</span>
                                    <span class='w-25 d-inline-block'>{{$his->weight}}</span>
                                </div>
                                <div style="letter-spacing: -1em;">
                                    <span class='w-25 d-inline-block'>{{$his->horseCount}}頭</span>
                                    <span class='w-25 d-inline-block'>{{$his->umaban}}番</span>
                                    <span class='w-50 d-inline-block'>{{$his->ninki . "人気(" . $his->odds .")"}}</span>
                                </div>
                                <div style="letter-spacing: -1em;">
                                    <span class='w-25 d-inline-block'>{{$his->time}}</span>
                                    <span class='w-75 d-inline-block'>{{$his->firstPace . " - " . $his->latterPace . "（" . $his->paceKbn . "） 上り " . $his->agari600m}}</span>
                                </div>
                                <div>{{$his->pointTime}}</div>
                                <div>{{$his->winHorse}}</div>
                            </div>
                        </div>
                    @endforeach
                @endforeach
                </div>
            </div>

            <div class="col-30">
            </div>

        </div>
    @else
        <div class="p-4 fs-4">
            <span>対象のデータが存在しません</span>
        </div>
    @endif
@endsection


@section('pageJs')
<script type="text/javascript">
</script>
@endsection


@section('pageCss')
<style>
    .col-40 { flex: 0 0 auto; width:40%; }
    .col-30 { flex: 0 0 auto; width:30%; }
    table {
        border-color: #dee2e6;
    }
    span.d-inline-block {
        letter-spacing: 0em;
    }
</style>
@endsection

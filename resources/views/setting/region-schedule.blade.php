@extends('common.base')
@section('pageTitle', '地方スケジュールの取得')


@section('pageContents')
    @include('common.menu-header', ['title' => '地方スケジュールの取得'])

    {{ Form::open(['route' => 'setting.get.regionschedule', 'method' => 'post']) }}
    <div class="p-4">
        <span class="item-name">取得期間</span>
        <input type="date" name="sta_date" value="{{$sta_dt}}">
        <span>～</span>
        <input type="date" name="end_date" value="{{$end_dt}}">
        <button type="submit">決定</button>
    </div>
    {{ Form::close() }}
@endsection


@section('pageCss')
@endsection


@section('pageJs')
@endsection

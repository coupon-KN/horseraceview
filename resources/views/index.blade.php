@extends('common.base')
@section('pageTitle', 'chestnut')

@section('pageJs')
@endsection

@section('pageContents')
    <div>設定</div>
    <a href="{{ route('setting.raceload') }}">レース情報取得</a>
@endsection

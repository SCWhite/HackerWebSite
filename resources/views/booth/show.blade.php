@extends('app')

@section('title')
    {{ $booth->name }} - 投票所資料
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">{{ $booth->name }} - 投票所資料</div>
                    {{-- Panel body --}}
                    <div class="panel-body">
                        <div class="row">
                            <div class="text-center col-md-12 col-md-offset-0">
                                <table class="table table-hover">
                                    <tr>
                                        <td>投票所名稱：</td>
                                        <td>{{ $booth->name }}</td>
                                    </tr>
                                    <tr>
                                        <td>直播頻道網址：</td>
                                        <td>{!! HTML::link($booth->url, $booth->url, ["target" => "_blank"]) !!}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            {!! HTML::linkRoute('booth.edit', '編輯投票所資料', $booth->id, ['class' => 'btn btn-primary']) !!}
                                            {!! HTML::linkRoute('booth.index', '返回投票所列表', [], ['class' => 'btn btn-default']) !!}
                                            {!! Form::open(['route' => ['booth.destroy', $booth->id], 'style' => 'display: inline', 'method' => 'DELETE',
                                            'onSubmit' => "return confirm('確定要刪除投票所嗎？');"]) !!}
                                            {!! Form::submit('刪除', ['class' => 'btn btn-danger']) !!}
                                            {!! Form::close() !!}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">票數統計</div>
                    {{-- Panel body --}}
                    <div class="panel-body">
                        <div class="row">
                            <div class="text-center col-md-12 col-md-offset-0">
                                @foreach($voteTypeList as $voteType)
                                    <h2>{{ $voteType }}</h2>
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th class="text-center">號碼</th>
                                                <th class="text-center">候選人</th>
                                                <th class="text-center">系級</th>
                                                <th class="text-center">票數</th>
                                                <th class="text-center">操作</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($candidateList[$voteType] as $candidate)
                                            <tr>
                                                <td>@if($candidate->canVote()){{ $candidate->number }}@endif</td>
                                                <td>{!! HTML::linkRoute('candidate.show', $candidate->name, $candidate->id, null) !!}</td>
                                                <td>{{ $candidate->department }}{{ $candidate->class }}</td>
                                                <td>@if($candidate->canVote()){{ $candidate->voteCount($booth->id) }}@endif</td>
                                                <td>
                                                    @if($candidate->canVote())
                                                        <a href="javascript:void(0)" class="btn btn-primary glyphicon glyphicon-plus"></a>
                                                        <a href="javascript:void(0)" class="btn btn-danger glyphicon glyphicon-minus"></a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

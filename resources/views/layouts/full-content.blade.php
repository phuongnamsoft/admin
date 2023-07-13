@section('content')
    <section class="content">
        @include('admin::partials.alerts')
        @include('admin::partials.exception')

        {!! $content !!}

        @include('admin::partials.toastr')
    </section>
@endsection

@section('app')
    {!! PNS\Admin\Admin::asset()->styleToHtml() !!}

    <div class="content-body" id="app">
        {{-- 页面埋点--}}
        {!! admin_section(PNS\Admin\Admin::SECTION['APP_INNER_BEFORE']) !!}

        @yield('content')

        {{-- 页面埋点--}}
        {!! admin_section(PNS\Admin\Admin::SECTION['APP_INNER_AFTER']) !!}
    </div>

    {!! PNS\Admin\Admin::asset()->scriptToHtml() !!}
    <div class="extra-html">{!! PNS\Admin\Admin::html() !!}</div>
@endsection


@if(!request()->pjax())
    @include('admin::layouts.full-page', ['header' => $header])
@else
    <title>{{ PNS\Admin\Admin::title() }} @if($header) | {{ $header }}@endif</title>

    <script>PNS.wait();</script>

    {!! PNS\Admin\Admin::asset()->cssToHtml() !!}
    {!! PNS\Admin\Admin::asset()->jsToHtml() !!}

    @yield('app')
@endif

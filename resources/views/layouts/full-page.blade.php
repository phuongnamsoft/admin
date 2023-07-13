<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="chrome=1,IE=edge">
    {{-- 默认使用谷歌浏览器内核--}}
    <meta name="renderer" content="webkit">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">

    <title>@if(! empty($header)){{ $header }} | @endif {{ PNS\Admin\Admin::title() }}</title>

    @if(! config('admin.disable_no_referrer_meta'))
        <meta name="referrer" content="no-referrer"/>
    @endif

    @if(! empty($favicon = PNS\Admin\Admin::favicon()))
        <link rel="shortcut icon" href="{{$favicon}}">
    @endif

    {!! admin_section(PNS\Admin\Admin::SECTION['HEAD']) !!}

    {!! PNS\Admin\Admin::asset()->headerJsToHtml() !!}

    {!! PNS\Admin\Admin::asset()->cssToHtml() !!}
</head>

<body class="pns-admin-body full-page {{ $configData['body_class'] }}">

<script>
    var PNS = CreatePNS({!! PNS\Admin\Admin::jsVariables() !!});
</script>

{{-- 页面埋点 --}}
{!! admin_section(PNS\Admin\Admin::SECTION['BODY_INNER_BEFORE']) !!}

<div class="app-content content">
    <div class="wrapper" id="{{ $pjaxContainerId }}">
        @yield('app')
    </div>
</div>

{!! admin_section(PNS\Admin\Admin::SECTION['BODY_INNER_AFTER']) !!}

{!! PNS\Admin\Admin::asset()->jsToHtml() !!}

<script>PNS.boot();</script>

</body>
</html>
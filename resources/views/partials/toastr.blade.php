@if(Session::has('pns-admin-toastr'))
    @php
        $toastr  = Session::get('pns-admin-toastr');
        $type    = $toastr->get('type')[0] ?? 'success';
        $message = $toastr->get('message')[0] ?? '';
        $options = admin_javascript_json($toastr->get('options', []));
    @endphp
    <script>$(function () { toastr.{{$type}}('{!!  $message  !!}', null, {!! $options !!}); })</script>
@endif
aaa

<div class="form-group row">
    <label class="col-sm-2 col-form-label">是否啟用</label>
        <input type="checkbox" name="my-checkbox" checked>
</div>

<link rel="stylesheet" href="{{asset('css/bootstrap-switch.css')}}">

<script src="{{asset('theme/cryptoadmin/vendor_components/jquery-3.3.1/jquery-3.3.1.js')}}"></script>

{{--<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">--}}
{{--<link href="css/highlight.css" rel="stylesheet">--}}
{{--<link href="https://unpkg.com/bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.css" rel="stylesheet">--}}
{{--<link rel="stylesheet" href="{{asset('theme/cryptoadmin/vendor_components/bootstrap-switch/switch.css')}}">--}}
{{--<link href="https://getbootstrap.com/assets/css/docs.min.css" rel="stylesheet">--}}
{{--<link href="css/main.css" rel="stylesheet">--}}
{{--<script src="{{asset('js/bootstrap-switch.js')}}"></script>--}}
<script src="https://unpkg.com/bootstrap-switch"></script>




<script type="text/javascript">
    $(function(){
        $("[name='my-checkbox']").bootstrapSwitch();
    })
</script>
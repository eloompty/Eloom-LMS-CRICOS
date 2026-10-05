@if ($errors->any())
<div class="alert alert-danger alert-block">
    <button type="button" class="close" data-dismiss="alert">×</button>
    @foreach ($errors->all() as $error)
    <strong>{{ $error }}</strong><br>
    @endforeach
</div>
@endif

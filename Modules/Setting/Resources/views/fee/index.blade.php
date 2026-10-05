@extends('user::layouts.master')
@section('title', 'Admin | Fee Settings')

@section('content')
@if ($text = Session::get('success'))
<div class="alert alert-success alert-block">
    <button type="button" class="close" data-dismiss="alert">×</button>
    <strong>{{ $text }}</strong>
</div>
@elseif ($text = Session::get('failure'))
<div class="alert alert-danger alert-block">
    <button type="button" class="close" data-dismiss="alert">×</button>
    <strong>{{ $text }}</strong>
</div>
@endif
<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Fee Settings</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Fee Settings</li>
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>

<!-- Main content -->
<section class="content">
    <!-- form start -->
    <form id="updatesetting" action="{{ route('admin.setting.fee.update') }}" method="POST">
        @csrf
        <div class="container-fluid">
            <div class="row">
                <!-- left column -->
                <div class="col-md-12">
                    <!-- jquery validation -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Fee Settings</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-sm-4">
                                    <label for="fee_module">Active Fee Module</label>
                                    <select name="fee_module" class="form-control" id="fee_module">
                                        <option @if(feeSetting('fee_module')=='yes' ) selected @endif value="yes">Yes</option>
                                        <option @if(feeSetting('fee_module')=='no' ) selected @endif value="no">No</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label for="payment_module">Active Payment Module</label>
                                    <select name="payment_module" class="form-control" id="payment_module">
                                        <option @if(feeSetting('payment_module')=='yes' ) selected @endif value="yes">Yes</option>
                                        <option @if(feeSetting('payment_module')=='no' ) selected @endif value="no">No</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label for="unit_wise_fee">Unit Wise Fee</label>
                                    <select name="unit_wise_fee" class="form-control" id="unit_wise_fee">
                                        <option @if(feeSetting('unit_wise_fee')=='yes' ) selected @endif value="yes">Yes</option>
                                        <option @if(feeSetting('unit_wise_fee')=='no' ) selected @endif value="no">No</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!--/.col (left) -->
        </div>
        <!-- /.row -->
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->
@endsection
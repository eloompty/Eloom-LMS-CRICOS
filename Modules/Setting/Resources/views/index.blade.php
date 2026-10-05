@extends('user::layouts.master')
@section('title', 'Admin | Settings')

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
                <h1>Settings</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Settings</li>
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- form start -->
        <form id="updatesetting" action="{{ route('admin.setting.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <!-- left column -->
                <div class="col-md-12">
                    <!-- jquery validation -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"> Settings</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="title">Title</label>
                                        <input type="text" name="title" class="form-control" id="title" placeholder="Enter Title" value="{{ getSettingValue('title') }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="date_format">Date Format</label>
                                        <select name="date_format" class="form-control" id="date_format">
                                            <option value="" selected disabled>-- Select Date Format --</option>
                                            <option @if(getSettingValue('date_format')=='Y-m-d' )selected @endif value="Y-m-d">{{ date('Y-m-d') }} (yyyy-mm-dd)</option>
                                            <option @if(getSettingValue('date_format')=='d/m/Y' )selected @endif value="d/m/Y">{{ date('d/m/Y') }} (dd/mm/yyyy)</option>
                                            <option @if(getSettingValue('date_format')=='m/d/Y' )selected @endif value="m/d/Y">{{ date('m/d/Y') }} (mm/dd/yyyy)</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="time_format">Time Format</label>
                                        <select name="time_format" class="form-control" id="time_format">
                                            <option value="" selected disabled>-- Select Time Format--</option>
                                            <option @if(getSettingValue('time_format')=='h:i A' )selected @endif value="h:i A">{{ date('h:i A') }}</option>
                                            <option @if(getSettingValue('time_format')=='h:i a' )selected @endif value="h:i a">{{ date('h:i a') }}</option>
                                            <option @if(getSettingValue('time_format')=='h:i:s' )selected @endif value="h:i:s">{{ date('h:i:s') }}</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="fcm_server_api_key">FCM Server API Key</label>
                                        <textarea name="fcm_server_api_key" class="form-control" id="fcm_server_api_key" placeholder="Enter FCM Server API Key">{{ getSettingValue('fcm_server_api_key') }}</textarea>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="logo">Logo</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" name="logo" class="custom-file-input" id="logo" onchange="readURL(this);">
                                                <label class="custom-file-label" for="logo">Choose file</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        @if (getSettingValue('logo'))
                                        <img src="{{ asset(getSettingValue('logo')) }}" id="box-image" alt="" style="width: 128px; border: #ebebeb 1px solid;">
                                        @else
                                        <img src="{{ asset('themes/AdminLTE/dist/img/boxed-bg.png') }}" id="box-image" alt="" style="width: 128px; border: #ebebeb 1px solid;">
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="fav_icon">Fav Icon</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" name="fav_icon" class="custom-file-input" id="fav_icon" onchange="readFavURL(this);">
                                                <label class="custom-file-label" for="fav_icon">Choose file</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        @if (getSettingValue('fav_icon'))
                                        <img src="{{ asset(getSettingValue('fav_icon')) }}" id="fav-box-image" alt="" style="width: 56px; border: #ebebeb 1px solid;">
                                        @else
                                        <img src="{{ asset('themes/AdminLTE/dist/img/boxed-bg.png') }}" id="fav-box-image" alt="" style="width: 56px; border: #ebebeb 1px solid;">
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-sm-6">
                                    <label for="student_id">Student ID</label>
                                    <select name="student_id" class="form-control" id="student_id">
                                        <option value="" selected disabled>-- Select Student ID--</option>
                                        <option @if(getSettingValue('student_id')=='Automatic' )selected @endif value="Automatic">Automatic</option>
                                        <option @if(getSettingValue('student_id')=='Manual' || getSettingValue('student_id')==NULL)selected @endif value="Manual">Manual</option>
                                    </select>
                                </div>
                                <div id="automaticFields" style="display: none;">
                                    <div class="form-group col-sm-6">
                                        <label for="student_id_format">Student ID Format</label>
                                        <select name="student_id_format" class="form-control" id="student_id_format">
                                            <option value="" selected disabled>-- Select Format --</option>
                                            <option value="Prefix" {{ getSettingValue('student_id_format') == 'Prefix' ? 'selected' : '' }}>Prefix</option>
                                            <option value="Suffix" {{ getSettingValue('student_id_format') == 'Suffix' ? 'selected' : '' }}>Suffix</option>
                                            <option value="Both" {{ getSettingValue('student_id_format') == 'Both' ? 'selected' : '' }}>Both</option>
                                            <option value="None" {{ getSettingValue('student_id_format') == 'None' ? 'selected' : '' }}>None</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-sm-6" id="prefixField" style="display: none;">
                                        <label for="student_id_prefix">Student ID Prefix</label>
                                        <input type="text" name="student_id_prefix" class="form-control" id="student_id_prefix" placeholder="Enter Prefix" value="{{ getSettingValue('student_id_prefix') }}">
                                    </div>

                                    <div class="form-group col-sm-6" id="suffixField" style="display: none;">
                                        <label for="student_id_suffix">Student ID Suffix</label>
                                        <input type="text" name="student_id_suffix" class="form-control" id="student_id_suffix" placeholder="Enter Suffix" value="{{ getSettingValue('student_id_suffix') }}">
                                    </div>

                                    <div class="form-group col-sm-6">
                                        <label for="student_id_number_start">Student ID Number Start</label>
                                        <input type="text" name="student_id_number_start" class="form-control" id="student_id_number_start" placeholder="Enter Student ID Number Start" value="{{ getSettingValue('student_id_number_start') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
                <!--/.col (left) -->
            </div>
            <!-- /.row -->
        </form>
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->
@endsection

@section('scripts')
<script>
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function(e) {
                $('#box-image')
                    .attr('src', e.target.result)
                    .width(128)
                    .height(128);
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    function readFavURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function(e) {
                $('#fav-box-image')
                    .attr('src', e.target.result)
                    .width(56)
                    .height(56);
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        function toggleFields() {
            const studentIdType = document.getElementById("student_id").value;
            const automaticFields = document.getElementById("automaticFields");

            automaticFields.style.display = studentIdType === "Automatic" ? "contents" : "none";
        }

        function togglePrefixSuffixFields() {
            const format = document.getElementById("student_id_format").value;
            document.getElementById("prefixField").style.display = (format === "Prefix" || format === "Both") ? "block" : "none";
            document.getElementById("suffixField").style.display = (format === "Suffix" || format === "Both") ? "block" : "none";
        }

        document.getElementById("student_id").addEventListener("change", toggleFields);
        document.getElementById("student_id_format").addEventListener("change", togglePrefixSuffixFields);

        // Initial call to set fields on page load
        toggleFields();
        togglePrefixSuffixFields();
    });
</script>
@endsection

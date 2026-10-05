@extends('user::layouts.master')
@section('title', 'Admin | Import Data')

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
                <h1>Import Data</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Import Data</li>
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <form id="deliverysite" method="POST" action="{{ route('admin.setting.rto.deliverysite') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <!-- left column -->
                <div class="col-md-12">
                    <!-- jquery validation -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Import Delivery Sites</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">

                            <div class="form-group">
                                <label for="csv">RTO Delivery Site CSV</label> <span class="required">*</span>
                                <input type="file" name="delivery_site" class="form-control" id="delivery_site" accept="text/csv">
                            </div>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </div>
                <!-- /.card -->
            </div>
        </form>
        <!-- /.row -->

        <form id="course" method="POST" action="{{ route('admin.setting.rto.course') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <!-- left column -->
                <div class="col-md-12">
                    <!-- jquery validation -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Import Courses</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">

                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="csv">RTO Course CSV</label> <span class="required">*</span>
                                    <input type="file" name="courses" class="form-control" id="courses" accept="text/csv">
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="csv">RTO Subject Course CSV</label> <span class="required">*</span>
                                    <input type="file" name="subject_courses" class="form-control" id="subject_courses" accept="text/csv">
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="csv">RTO Unit CSV</label> <span class="required">*</span>
                                    <input type="file" name="units" class="form-control" id="units" accept="text/csv">
                                </div>

                            </div>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </div>
                <!-- /.card -->
            </div>
        </form>
        <!-- /.row -->

        <form id="intake" method="POST" action="{{ route('admin.setting.rto.intake') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <!-- left column -->
                <div class="col-md-12">
                    <!-- jquery validation -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Import Intakes</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">

                            <div class="form-group">
                                <label for="csv">RTO Intake CSV</label> <span class="required">*</span>
                                <input type="file" name="intake" class="form-control" id="intake" accept="text/csv">
                            </div>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </div>
                <!-- /.card -->
            </div>
        </form>
        <!-- /.row -->

        <form id="student" method="POST" action="{{ route('admin.setting.rto.student') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <!-- left column -->
                <div class="col-md-12">
                    <!-- jquery validation -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Import Students</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="csv">RTO Student CSV</label> <span class="required">*</span>
                                    <input type="file" name="student" class="form-control" id="student" accept="text/csv">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="csv">RTO Student Address CSV</label> <span class="required">*</span>
                                    <input type="file" name="student_address" class="form-control" id="student_address" accept="text/csv">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="csv">RTO Student Schooling CSV</label> <span class="required">*</span>
                                    <input type="file" name="student_schooling" class="form-control" id="student_schooling" accept="text/csv">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="csv">RTO Student Contact CSV</label> <span class="required">*</span>
                                    <input type="file" name="student_contact" class="form-control" id="student_contact" accept="text/csv">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="csv">RTO Student Service CSV</label> <span class="required">*</span>
                                    <input type="file" name="student_service" class="form-control" id="student_service" accept="text/csv">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="csv">RTO Student Course CSV</label> <span class="required">*</span>
                                    <input type="file" name="student_course" class="form-control" id="student_course" accept="text/csv">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="csv">RTO Student Result CSV</label> <span class="required">*</span>
                                    <input type="file" name="student_result" class="form-control" id="student_result" accept="text/csv">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="csv">RTO Student Unit Result CSV</label> <span class="required">*</span>
                                    <input type="file" name="student_result_unit" class="form-control" id="student_result_unit" accept="text/csv">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="csv">RTO Student Certificate CSV</label> <span class="required">*</span>
                                    <input type="file" name="student_certificate" class="form-control" id="student_certificate" accept="text/csv">
                                </div>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
        </form>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->
@endsection

@section('scripts')
<!-- jquery-validation -->
<script src="{{ asset('themes/AdminLTE/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('themes/AdminLTE/plugins/jquery-validation/additional-methods.min.js') }}"></script>

<script>
    $(function() {
        $('#deliverysite').validate({
            rules: {
                delivery_site: {
                    required: true,
                },
            },
            messages: {
                delivery_site: "Please upload delivery site csv",
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });

        $('#course').validate({
            rules: {
                courses: {
                    required: true,
                },
                subject_courses: {
                    required: true,
                },
                units: {
                    required: true,
                },
            },
            messages: {
                courses: "Please upload course csv",
                subject_courses: "Please upload subject course csv",
                units: "Please upload unit csv",
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });

        $('#intake').validate({
            rules: {
                intake: {
                    required: true,
                },
            },
            messages: {
                intake: "Please upload intake csv",
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });

        $('#student').validate({
            rules: {
                student: {
                    required: true,
                },
                student_address: {
                    required: true,
                },
                student_schooling: {
                    required: true,
                },
                student_contact: {
                    required: true,
                },
                student_service: {
                    required: true,
                },
                student_course: {
                    required: true,
                },
                student_result: {
                    required: true,
                },
                student_result_unit: {
                    required: true,
                },
                student_certificate: {
                    required: true,
                },
            },
            messages: {
                student: "Please upload student csv",
                student_address: "Please upload student address csv",
                student_schooling: "Please upload student schooling csv",
                student_contact: "Please upload student contact csv",
                student_service: "Please upload student service csv",
                student_result: "Please upload student result csv",
                student_result_unit: "Please upload student unit result csv",
                student_certificate: "Please upload student certificate csv",
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });
    });
</script>
@endsection

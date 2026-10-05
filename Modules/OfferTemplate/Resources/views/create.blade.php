@extends('user::layouts.master')
@section('title', 'Admin | Upload Offer Template')

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Create Offer Template</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.setting.menu') }}">Settings</a></li>
                    <li class="breadcrumb-item active">Offer Template</li>
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>

<section class="content">
    <div class="container">
        <h2 class="mb-4">Create Offer Template</h2>
        <!-- File Upload Form -->
        <div class="card mb-4">
            <div class="card-header">Upload Template</div>
            <div class="card-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="form-group col-sm-3">
                            <label for="name">Template Name</label> <span class="required">*</span>
                            <input type="text" name="name" class="form-control" id="name" placeholder="Enter Template Name">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="docxFile" class="form-label">Select DOCX File</label>
                        <input type="file" class="form-control" id="docxFile" name="docxFile" required accept=".docx">
                    </div>
                    <button type="submit" class="btn btn-primary">Upload & Convert</button>

                    <!-- Loader -->
                    <div id="loader" class="mt-3" style="display: none;">
                        <div class="spinner-border text-primary mb-2" role="status">
                            <span class="visually-hidden">...</span>
                        </div>
                        <p>Loading HTML, please wait...</p>
                    </div>
                </form>
            </div>
        </div>

        <!-- HTML Editor (Hidden initially) -->
        <div class="card mt-4" id="editorContainer" style="display: none;">
            <div class="card-header">Edit Converted HTML</div>
            <div class="card-body">
                <textarea id="htmlEditor"></textarea>
                <button id="saveHtml" class="btn btn-success mt-3">Save HTML</button>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<!-- Include CKEditor -->
<script src="https://cdn.ckeditor.com/4.19.1/full/ckeditor.js"></script>

<script>
    const templateName = "Upload Offer Template";

    document.addEventListener("DOMContentLoaded", function() {
        let htmlContent = `{!! isset($htmlContent) ? addslashes($htmlContent) : '' !!}`;

        if (htmlContent.trim() !== "") {
            document.getElementById('editorContainer').style.display = 'block';
            CKEDITOR.replace('htmlEditor');
            CKEDITOR.instances.htmlEditor.setData(htmlContent);
        } else {
            document.getElementById('editorContainer').style.display = 'none';
        }
    });

    document.getElementById('uploadForm').addEventListener('submit', async function(event) {
        event.preventDefault();

        let formData = new FormData();
        let fileInput = document.getElementById('docxFile');
        formData.append('docxFile', fileInput.files[0]);

        // Show loader
        document.getElementById('loader').style.display = 'block';

        try {
            let response = await fetch('{{ route("admin.offer.template.convert") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            let result = await response.json();
            // Hide loader after receiving response
            document.getElementById('loader').style.display = 'none';
            if (result.success) {
                document.getElementById('editorContainer').style.display = 'block';
                CKEDITOR.replace('htmlEditor');
                CKEDITOR.instances.htmlEditor.setData(result.htmlContent);
            } else {
                alert('Conversion failed: ' + result.message);
            }
        } catch (error) {
            // Hide loader in case of error
            document.getElementById('loader').style.display = 'none';
            alert('Error uploading file');
        }
    });

    document.getElementById('saveHtml').addEventListener('click', async function() {
        // Save HTML
        const name = document.getElementById('name').value;
        console.log("name: ", name);
        if (!name) {
            alert('name is required');
            return
        }
        let editedHtml = CKEDITOR.instances.htmlEditor.getData();
        let response = await fetch('{{ route("admin.offer.template.saveHtml") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                htmlContent: editedHtml,
                name: name
            })
        });

        let result = await response.json();
        if (result.success) {
            alert('HTML content saved successfully!');
            window.location.href = "/admin/offer/template";
        } else {
            alert('Failed to save HTML: ' + result.message);
        }
    });
</script>
@endsection
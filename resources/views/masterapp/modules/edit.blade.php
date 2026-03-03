
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-10">
                <div class="card">

                  
                    <!-- /.card-header -->
                    <div class="card-body">
                       <form action="{{ route('masterapp.modules.update', $module) }}" method="POST" id="form-edit-module">
                        @csrf
                        @method('PUT')
                        @include('masterapp.modules.form')

                            <!-- General error message container -->
                            <div class="alert alert-danger d-none" id="form-error-message"></div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary" id="btn-edit-module-submit">
                                <span id="btn-edit-text">Update Module</span>
                                <span id="btn-edit-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                            </div>
                        </form>

                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div>
</section>

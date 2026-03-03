
<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">

                  
                    <!-- /.card-header -->
                    <div class="card-body">
                       <form action="{{ route('masterapp.roles.update', $role->id) }}" method="POST"  id="form-edit-role">
                         @csrf
                         @method('PUT')
                        @include('masterapp.roles.form')

                            <!-- General error message container -->
                            <div class="alert alert-danger d-none" id="form-error-message"></div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

                                <button type="submit" class="btn btn-success" id="submit-btn">
                                    <span id="btn-text">Update Role</span>
                                    <span id="btn-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
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

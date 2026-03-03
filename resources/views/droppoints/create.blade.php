<!-- Your HTML remains the same, just ensure the IDs and selectors are correct -->
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Create New Drop point</div>
                 <div class="card-body" id="form-content">
                    <form action="{{ route('droppoints.store') }}" method="POST" id="form-droppoint">
                        @csrf
                        @include('droppoints.form')

                        <!-- General error message container -->
                        <div class="alert alert-danger d-none" id="form-error-message"></div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('droppoints.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success" id="submit-btn">
                                <span id="btn-text">Create Droppoint</span>
                                <span id="btn-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

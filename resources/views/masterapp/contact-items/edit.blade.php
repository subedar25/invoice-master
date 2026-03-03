<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-10">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('masterapp.contact-items.update', $contactItem->id) }}" method="POST" id="form-edit-contact-item">
                            @csrf
                            @method('PUT')
                            @include('masterapp.contact-items.form')

                            <div class="alert alert-danger d-none" id="form-error-message"></div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-success" id="submit-btn">
                                    <span id="btn-text">Update Item</span>
                                    <span id="btn-edit-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

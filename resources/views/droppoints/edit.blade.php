<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Edit DropPoint: {{ $droppoint->name }}</div>

                <div class="card-body">
                   
                   <!-- <form action="{{-- route('droppoints.update',) --}}" method="POST" id="form-modules"> -->
                    <form action="{{ route('droppoints.update', $droppoint) }}" method="POST" id="form-edit-droppoint">
                        @csrf
                        @method('PUT')
                        
                        @include('droppoints.form')

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('droppoints.index') }}" class="btn btn-secondary">Cancel</a>
                            
                            <!-- Updated button text and IDs -->
                            <button type="submit" class="btn btn-primary" id="btn-edit-droppoint-submit">
                                <span id="btn-edit-text">Update DropPoint</span>
                                <span id="btn-edit-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
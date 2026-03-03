<div class="mb-3">
    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="name" name="name" value="{{ $module->name ?? '' }}" required>
    <!-- The invalid-feedback div will be added by JavaScript if there's an error -->
</div>

<div class="mb-3">
    <label for="slug" class="form-label">Slug</label>
    <input type="text" class="form-control" id="slug" name="slug" value="{{ $module->slug ?? '' }}" required>
    <!-- The invalid-feedback div will be added by JavaScript if there's an error -->
</div>

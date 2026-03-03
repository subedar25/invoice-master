<div class="mb-3">
    <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
    <select id="type" name="type" class="form-control" required>
        <option value="">Select type</option>
        <option value="phone" {{ isset($contactItem) && $contactItem->type === 'phone' ? 'selected' : '' }}>Phone</option>
        <option value="email" {{ isset($contactItem) && $contactItem->type === 'email' ? 'selected' : '' }}>Email</option>
        <option value="other" {{ isset($contactItem) && $contactItem->type === 'other' ? 'selected' : '' }}>Other</option>
    </select>
</div>
@if (isset($contactItem))
    <input type="hidden" name="id" value="{{ $contactItem->id }}">
@endif

<div class="mb-3">
    <label for="value" class="form-label">Value <span class="text-danger">*</span></label>
    @php
        $rawValue = isset($contactItem) ? $contactItem->value : '';
        if (isset($contactItem) && $contactItem->type === 'phone') {
            $digits = preg_replace('/\D+/', '', $rawValue ?? '');
            $digits = substr($digits, 0, 10);
            $formattedParts = [];
            if (strlen($digits) > 0) $formattedParts[] = substr($digits, 0, 3);
            if (strlen($digits) > 3) $formattedParts[] = substr($digits, 3, 3);
            if (strlen($digits) > 6) $formattedParts[] = substr($digits, 6, 4);
            $rawValue = implode('-', $formattedParts);
        }
    @endphp
    <input type="text" id="value" name="value" value="{{ $rawValue }}" class="form-control" required data-phone-mask>
</div>

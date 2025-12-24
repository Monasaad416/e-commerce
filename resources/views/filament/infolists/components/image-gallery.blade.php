@php
    $state = $getState() ?? [];
    $images = collect($state['images'] ?? []);
@endphp

@if($images->isEmpty())
    <div style="padding: 1rem; text-align: center; color: #6b7280; background-color: #f9fafb; border-radius: 0.5rem;">
        {{ __('general.no_images') }}
    </div>
@else
    <div style="display: flex; flex-wrap: wrap; gap: 1rem; padding: 0.5rem;">
        @foreach($images as $image)
            <div style="position: relative;">
                <img
                    src="{{ $image['url'] }}"
                    alt="Product image"
                    style="width: 10rem; height: 10rem; object-fit: cover; border-radius: 0.5rem; border: 1px solid #e5e7eb;"
                    onerror="this.onerror=null; this.src='{{ asset('images/placeholder.jpg') }}'"
                >
                @if($image['is_primary'] ?? false)
                    <span style="position: absolute; top: 0.5rem; right: 0.5rem; background-color: #3b82f6; color: white; font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 9999px;">
                        {{ __('filament/admin/product_resource.featured_image') }}
                    </span>
                @endif
            </div>
        @endforeach
    </div>
@endif
{{-- resources/views/components/skeleton.blade.php --}}
@props([
    'type' => 'text',  // text, block, avatar
    'width' => '100%',
    'height' => null,
    'class' => '',
])

@php
    // Setup dasar class Bootstrap 5
    $baseClass = 'placeholder';

    // Logika styling tambahan
    $styles = "width: {$width};";

    if ($height) {
        $styles .= " height: {$height};";
    }

    // Variasi bentuk
    if ($type === 'avatar') {
        $classes = "$baseClass rounded-circle bg-secondary";
        // Avatar biasanya butuh rasio 1:1, pastikan width/height diatur saat panggil
    } elseif ($type === 'block') {
        $classes = "$baseClass d-block bg-secondary"; // Block elemen
    } else {
        $classes = "$baseClass"; // Default text line
    }
@endphp

{{-- Wrapper untuk animasi Glow/Wave --}}
<p class="placeholder-glow m-0">
    <span
        {{ $attributes->merge(['class' => "$classes $class"]) }}
        style="{{ $styles }}"
        aria-hidden="true">
    </span>
</p>

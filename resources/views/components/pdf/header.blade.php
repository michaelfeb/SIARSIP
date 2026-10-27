@php
    $logoPath = $logoPath ?? public_path('images/logo-ulm.png');
    $ministry = $ministry ?? 'KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI';
    $university = $university ?? 'UNIVERSITAS LAMBUNG MANGKURAT';
    $faculty = $faculty ?? 'FAKULTAS MATEMATIKA DAN ILMU PENGETAHUAN ALAM';
    $addressLines = $addressLines ?? [
        'Jalan A. Yani Km 35,8 Banjarbaru 70714',
        'Telepon: (0511) 4773112 Laman: https://fmipa.ulm.ac.id/',
    ];
@endphp

<header class="letterhead">
    <div class="letterhead-logo">
        <img src="{{ $logoPath }}" alt="Logo {{ $university }}">
    </div>
    <div class="letterhead-content">
        <p class="ministry">{{ $ministry }}</p>
        <p class="university">{{ $university }}</p>
        <p class="faculty">{{ $faculty }}</p>
        <p class="address">
            @foreach ($addressLines as $line)
                {{ $line }}@if (! $loop->last)<br>@endif
            @endforeach
        </p>
    </div>
</header>

<hr class="letterhead-rule">

@props([
    'title',
    'value',
    'icon' => null,
    'color' => 'blue',
])

@php
    $colorMap = [
        'blue'   => ['border' => 'border-l-sky-500',   'bg' => 'bg-sky-50',    'text' => 'text-sky-700',   'iconBg' => 'text-sky-600'],
        'green'  => ['border' => 'border-l-emerald-500','bg' => 'bg-emerald-50','text' => 'text-emerald-700','iconBg' => 'text-emerald-600'],
        'yellow' => ['border' => 'border-l-amber-500', 'bg' => 'bg-amber-50',  'text' => 'text-amber-700',  'iconBg' => 'text-amber-600'],
        'red'    => ['border' => 'border-l-rose-500',   'bg' => 'bg-rose-50',   'text' => 'text-rose-700',   'iconBg' => 'text-rose-600'],
        'purple' => ['border' => 'border-l-purple-500','bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'iconBg' => 'text-purple-600'],
        'teal'   => ['border' => 'border-l-teal-500',  'bg' => 'bg-teal-50',   'text' => 'text-teal-700',   'iconBg' => 'text-teal-600'],
    ];

    $c = $colorMap[$color] ?? $colorMap['blue'];
@endphp

<div {{ $attributes->merge(['class' => "bg-white rounded-2xl shadow-xs border border-slate-200/80 border-l-4 {$c['border']} p-5 hover:shadow-md transition-all duration-200"]) }}>
    <div class="flex items-center gap-4">
        {{-- Icon --}}
        <div class="flex-shrink-0 w-12 h-12 {{ $c['bg'] }} {{ $c['iconBg'] }} rounded-xl flex items-center justify-center font-bold">
            @if($icon)
                <span class="text-xl leading-none">{{ $icon }}</span>
            @else
                <svg class="w-6 h-6 stroke-current" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            @endif
        </div>

        {{-- Content --}}
        <div class="min-w-0 flex-1">
            <p class="text-2xl font-extrabold text-slate-900 leading-tight">{{ $value }}</p>
            <p class="text-xs sm:text-sm font-medium text-slate-500 mt-0.5 truncate">{{ $title }}</p>
        </div>
    </div>

    {{-- Optional slot for additional info (e.g., trend indicator) --}}
    @if($slot->isNotEmpty())
        <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500">
            {{ $slot }}
        </div>
    @endif
</div>

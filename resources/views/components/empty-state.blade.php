@props([
    'message' => 'Belum ada data',
    'title' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-14 px-6 text-center']) }}>
    {{-- Clean Minimalist Outline Icon --}}
    <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mb-4 text-slate-400 border border-slate-200/80">
        {{ $icon ?? '' }}
        @if(!isset($icon))
            <svg class="w-8 h-8 stroke-current" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
        @endif
    </div>

    @if($title)
        <h4 class="text-sm font-bold text-slate-800 mb-1">{{ $title }}</h4>
    @endif

    {{-- Message --}}
    <p class="text-slate-500 text-xs sm:text-sm leading-relaxed max-w-sm">
        {{ $message }}
    </p>

    {{-- Optional action slot --}}
    @if($slot->isNotEmpty())
        <div class="mt-5">
            {{ $slot }}
        </div>
    @endif
</div>

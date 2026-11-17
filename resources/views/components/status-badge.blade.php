@props(['status', 'type' => 'bill'])

@php
    // Status label mappings (Indonesian)
    $labels = [
        'unpaid'      => 'Belum Lunas',
        'paid'        => 'Lunas',
        'overdue'     => 'Jatuh Tempo',
        'pending'     => 'Menunggu',
        'verified'    => 'Terverifikasi',
        'in_progress' => 'Sedang Ditangani',
        'resolved'    => 'Selesai',
        'rejected'    => 'Ditolak',
        'cancelled'   => 'Dibatalkan',
    ];

    // Color mappings by type and status
    $colors = [
        'bill' => [
            'unpaid'  => 'bg-yellow-100 text-yellow-800 ring-1 ring-inset ring-yellow-600/20',
            'paid'    => 'bg-green-100 text-green-800 ring-1 ring-inset ring-green-600/20',
            'overdue' => 'bg-red-100 text-red-800 ring-1 ring-inset ring-red-600/20',
        ],
        'payment' => [
            'pending'   => 'bg-yellow-100 text-yellow-800 ring-1 ring-inset ring-yellow-600/20',
            'verified'  => 'bg-green-100 text-green-800 ring-1 ring-inset ring-green-600/20',
            'rejected'  => 'bg-red-100 text-red-800 ring-1 ring-inset ring-red-600/20',
            'cancelled' => 'bg-gray-100 text-gray-800 ring-1 ring-inset ring-gray-600/20',
        ],
        'complaint' => [
            'pending'     => 'bg-yellow-100 text-yellow-800 ring-1 ring-inset ring-yellow-600/20',
            'verified'    => 'bg-blue-100 text-blue-800 ring-1 ring-inset ring-blue-600/20',
            'in_progress' => 'bg-blue-100 text-blue-800 ring-1 ring-inset ring-blue-600/20',
            'resolved'    => 'bg-green-100 text-green-800 ring-1 ring-inset ring-green-600/20',
            'rejected'    => 'bg-red-100 text-red-800 ring-1 ring-inset ring-red-600/20',
        ],
    ];

    $label = $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
    $colorClass = $colors[$type][$status] ?? 'bg-gray-100 text-gray-800 ring-1 ring-inset ring-gray-600/20';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$colorClass}"]) }}>
    {{ $label }}
</span>

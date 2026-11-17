@if ($errors->any())
<div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
    <strong>Pemeriksaan belum disimpan.</strong><ul class="mt-2 list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    <a href="{{ url()->current() }}" class="mt-2 inline-block underline">Muat ulang data terbaru</a>
</div>
@endif

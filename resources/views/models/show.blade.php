@extends('layouts.app')
@section('title', $model->name)
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('models.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">Back</a>
    <span class="text-gray-200">/</span>
    <h2 class="font-display text-xl font-bold">{{ $model->name }}</h2>
    <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $model->status === 'Active' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $model->status }}</span>
    <div class="ml-auto flex gap-2">
        <a href="{{ route('models.edit', $model) }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-50 transition-colors">Edit</a>
        <form method="POST" action="{{ route('models.destroy', $model) }}" onsubmit="return confirm('Archive this model?')">
            @csrf @method('DELETE')
            <button class="px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm hover:bg-red-50 transition-colors">Archive</button>
        </form>
    </div>
</div>

<div class="grid grid-cols-3 gap-6">

    {{-- Left column --}}
    <div class="col-span-1 space-y-4">

        {{-- Photo --}}
        <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
            <div class="w-full h-56 bg-gradient-to-br from-[#0B132B] to-[#2C3E6B] flex items-center justify-center">
                @if($model->getFirstMediaUrl('portfolio'))
                    <img src="{{ $model->getFirstMediaUrl('portfolio') }}" class="w-full h-full object-cover">
                @else
                    <span class="font-display text-5xl font-bold text-[#C9A96E]/40">{{ strtoupper(substr($model->name,0,2)) }}</span>
                @endif
            </div>
            <div class="p-4">
                <h3 class="font-display font-bold text-lg">{{ $model->name }}</h3>
                @if($model->location)<p class="text-sm text-gray-400 mt-0.5">{{ $model->location }}</p>@endif
                <div class="flex flex-wrap gap-1 mt-2">
                    @foreach((array)$model->categories as $cat)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-[#0B132B]/8 text-[#0B132B]">{{ $cat }}</span>
                    @endforeach
                    @if($model->is_inhouse)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-green-50 text-green-700">In-house</span>
                    @endif
                </div>
                @if($model->about)
                    <p class="text-sm text-gray-600 leading-relaxed mt-3">{{ $model->about }}</p>
                @endif
            </div>
        </div>

        {{-- Measurements card --}}
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <h4 class="font-display font-semibold text-xs uppercase tracking-widest text-gray-400 mb-4">Measurements</h4>
            @if($model->height || $model->bust || $model->waist || $model->hips || $model->shoe_size)
            <div class="grid grid-cols-2 gap-3">
                @if($model->height)
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-[10px] text-gray-400 uppercase tracking-wide mb-1">Height</p>
                    <p class="font-display font-bold text-base text-[#0B132B]">{{ $model->height }}</p>
                </div>
                @endif
                @if($model->bust)
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-[10px] text-gray-400 uppercase tracking-wide mb-1">Bust</p>
                    <p class="font-display font-bold text-base text-[#0B132B]">{{ $model->bust }}</p>
                </div>
                @endif
                @if($model->waist)
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-[10px] text-gray-400 uppercase tracking-wide mb-1">Waist</p>
                    <p class="font-display font-bold text-base text-[#0B132B]">{{ $model->waist }}</p>
                </div>
                @endif
                @if($model->hips)
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-[10px] text-gray-400 uppercase tracking-wide mb-1">Hips</p>
                    <p class="font-display font-bold text-base text-[#0B132B]">{{ $model->hips }}</p>
                </div>
                @endif
                @if($model->shoe_size)
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <p class="text-[10px] text-gray-400 uppercase tracking-wide mb-1">Shoe Size</p>
                    <p class="font-display font-bold text-base text-[#0B132B]">{{ $model->shoe_size }}</p>
                </div>
                @endif
            </div>
            @else
            <p class="text-sm text-gray-400 text-center py-3">No measurements recorded.</p>
            @endif
        </div>

        {{-- Contact & details --}}
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <h4 class="font-display font-semibold text-xs uppercase tracking-widest text-gray-400 mb-3">Details</h4>
            <dl class="space-y-2.5 text-sm">
                @if($model->age)
                <div class="flex justify-between items-center">
                    <dt class="text-gray-400">Age</dt>
                    <dd class="font-medium">{{ $model->age }} years</dd>
                </div>
                @endif
                @if($model->email)
                <div class="flex justify-between items-center">
                    <dt class="text-gray-400">Email</dt>
                    <dd><a href="mailto:{{ $model->email }}" class="text-[#C9A96E] hover:underline text-xs">{{ $model->email }}</a></dd>
                </div>
                @endif
                @if($model->phone)
                <div class="flex justify-between items-center">
                    <dt class="text-gray-400">Phone</dt>
                    <dd class="font-medium">{{ $model->phone }}</dd>
                </div>
                @endif
                @if($model->budget)
                <div class="flex justify-between items-center">
                    <dt class="text-gray-400">Day Rate</dt>
                    <dd class="font-display font-bold">AED {{ number_format($model->budget) }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

    {{-- Right columns --}}
    <div class="col-span-2 space-y-4">

        {{-- Portfolio gallery --}}
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-display font-semibold text-xs uppercase tracking-widest text-gray-400">Portfolio</h4>
                <a href="{{ route('models.edit', $model) }}" class="text-xs text-[#C9A96E] hover:underline">+ Add photos</a>
            </div>
            @if($model->getMedia('portfolio')->count())
            <div class="grid grid-cols-3 gap-2">
                @foreach($model->getMedia('portfolio') as $media)
                <div class="aspect-square rounded-lg overflow-hidden cursor-pointer hover:opacity-90 transition-opacity"
                     onclick="openLightbox('{{ $media->getUrl() }}')">
                    <img src="{{ $media->getUrl('thumb') ?: $media->getUrl() }}" class="w-full h-full object-cover"
                         onerror="this.src='{{ $media->getUrl() }}'">
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-10 text-gray-300 border-2 border-dashed border-gray-100 rounded-xl">
                <p class="text-3xl mb-2">?</p>
                <p class="text-sm text-gray-400">No portfolio images yet.</p>
                <a href="{{ route('models.edit', $model) }}" class="text-xs text-[#C9A96E] mt-1 inline-block">Upload photos</a>
            </div>
            @endif
        </div>

        {{-- Projects --}}
        <div class="bg-white border border-gray-100 rounded-xl p-5">
            <h4 class="font-display font-semibold text-xs uppercase tracking-widest text-gray-400 mb-3">Projects ({{ $model->projects->count() }})</h4>
            @if($model->projects->count())
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-100">
                    <th class="text-left py-2 text-xs text-gray-400 font-medium">Project</th>
                    <th class="text-left py-2 text-xs text-gray-400 font-medium">Status</th>
                    <th class="text-right py-2 text-xs text-gray-400 font-medium">Budget</th>
                </tr></thead>
                <tbody>
                    @foreach($model->projects as $p)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="py-2.5">
                            <a href="{{ route('projects.show', $p) }}" class="hover:text-[#C9A96E] font-medium">{{ $p->title }}</a>
                            <p class="text-xs text-gray-400">{{ $p->brand?->name }}</p>
                        </td>
                        <td class="py-2.5">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $p->status === 'Active' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $p->status }}</span>
                        </td>
                        <td class="py-2.5 text-right font-mono text-sm">AED {{ number_format($p->budget) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="text-sm text-gray-400">Not assigned to any projects yet.</p>
            @endif
        </div>

    </div>
</div>

{{-- Lightbox --}}
<div id="lightbox-wrap" class="hidden fixed inset-0 bg-black/85 z-50 flex items-center justify-center p-4"
     onclick="closeLightbox()">
    <img id="lightbox-img" class="max-h-[90vh] max-w-[90vw] rounded-xl object-contain">
</div>

<script>
function openLightbox(url) {
    document.getElementById('lightbox-img').src = url;
    document.getElementById('lightbox-wrap').classList.remove('hidden');
    document.getElementById('lightbox-wrap').classList.add('flex');
}
function closeLightbox() {
    document.getElementById('lightbox-wrap').classList.add('hidden');
    document.getElementById('lightbox-wrap').classList.remove('flex');
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });
</script>
@endsection

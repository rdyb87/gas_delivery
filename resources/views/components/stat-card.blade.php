@props(['title', 'value', 'icon' => 'fa-box', 'color' => 'from-blue-500 to-blue-600'])

<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="bg-gradient-to-r {{ $color }} p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm opacity-90 mb-1">{{ $title }}</p>
                <p class="text-4xl font-bold">{{ $value }}</p>
            </div>
            <div class="opacity-80">
                <i class="fas {{ $icon }} text-3xl"></i>
            </div>
        </div>
    </div>
</div>
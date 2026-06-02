@props(['text', 'icon', 'count', 'color', 'type' => 'default', 'withFilter' => false, 'filterDefault' => ''])

<div class="card border-0 shadow-sm rounded-4 m-0 h-100 position-relative">
    <div class="card-body d-flex flex-column">
        <div class="row no-gutters align-items-center mb-2">
            <div class="col mr-2">
                <a href="{{ $url }}" class="text-xs font-weight-bold {{ $color }} text-uppercase mb-1 stretched-link text-decoration-none">
                    {{ $text }}
                </a>
            </div>
            <div class="col-auto d-flex align-items-center gap-2">
                @if($withFilter)
                <x-filter-date styleType="full" style="z-index: 3; position: relative;" default="{{ $filterDefault }}" />
                @endif
                <i class="bi {{ $icon }} {{ $color }} fs-4"></i>
            </div>
        </div>
        <div class="d-flex h-100 align-items-center justify-content-center">
            @if($type === 'list')
                @foreach($count as $item)
                    <div class="d-flex flex-column mx-3 text-center" title="{{ $item['text'] }}">
                        <div class="d-flex align-items-center gap-2 justify-content-center">
                            {{-- <i class="bi {{ $item['icon'] }} fs-4 {{ isset($item['color']) ? $item['color'] : 'text-muted' }}"></i> --}}
                            <div class="fs-4 text-sm {{ isset($item['color']) ? $item['color'] : 'text-muted' }}">
                                {{ $item['count'] }}
                            </div>
                        </div>
                        @if(isset($item['price']))
                            <div class="fw-bold {{ isset($item['color']) ? $item['color'] : 'text-muted' }} mb-1" style="font-size: 0.75rem;">
                                {{ $item['price'] }}
                            </div>
                        @endif
                        <small class="{{ isset($item['color']) ? $item['color'] : 'text-muted' }}" style="font-size: 0.7rem;">{{ $item['text'] }}</small>
                    </div>
                @endforeach
            @else
                <div class="display-4 font-weight-bold {{ $color }}">
                    {{ $count }}
                </div>
            @endif
        </div>
    </div>
</div>

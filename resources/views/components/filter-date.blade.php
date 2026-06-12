<div {{ $attributes->merge(['class' => 'dropdown']) }}>
    @if($styleType === 'minimal')
        <button class="btn btn-sm btn-link text-muted p-0 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-three-dots-vertical"></i>
        </button>
    @elseif($styleType === 'global')
        <button class="btn btn-white bg-white shadow-sm btn-sm dropdown-toggle border" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-calendar3 me-1 text-primary"></i> <span id="globalFilterText">{{$default}}</span>
        </button>
    @else
        <button class="btn btn-sm btn-white border shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-funnel"></i> <span class="widget-filter-text">{{$default}}</span>
        </button>
    @endif
    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
        <li><h6 class="dropdown-header">{{ $styleType === 'global' ? 'Filter Global' : 'Filter Waktu' }}</h6></li>
        <li>
            <div class="px-3 py-1">
                <input type="text"
                    class="form-control form-control-sm widget-custom-date bg-white"
                    placeholder="Pilih Tanggal..."
                    readonly
                    style="cursor: pointer;"
                    data-scope="{{ $styleType === 'global' ? 'global' : 'widget' }}">
            </div>
        </li>
        <li><a class="dropdown-item filter-btn" data-type="{{ $styleType }}" href="#" onclick="event.preventDefault(); applyFilter(this, 'today', 'Hari Ini')">Hari Ini</a></li>
        <li><a class="dropdown-item filter-btn" data-type="{{ $styleType }}" href="#" onclick="event.preventDefault(); applyFilter(this, 'weekly', 'Minggu Ini')">Minggu Ini</a></li>
        <li><a class="dropdown-item filter-btn" data-type="{{ $styleType }}" href="#" onclick="event.preventDefault(); applyFilter(this, 'monthly', 'Bulan Ini')">Bulan Ini</a></li>
        <li><a class="dropdown-item filter-btn" data-type="{{ $styleType }}" href="#" onclick="event.preventDefault(); applyFilter(this, 'yearly', 'Tahun Ini')">Tahun Ini</a></li>
    </ul>
</div>
<div class="card border-0 shadow-sm rounded-4 h-100">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
        <div class="w-50 mr-2">
            <span class="placeholder col-12 rounded"></span>
        </div>
    </div>
    <div class="card-body d-flex flex-column" style="height: 400px;">
        @for($i = 0; $i < 3; $i++)
        <div class="d-flex placeholder-glow mb-2 h-100" >
            <span class="placeholder col-12 rounded"></span>
        </div>
        @endfor
    </div>
</div>

<div class="pagination">
    @if($paginator->hasPages())
        <a href="{{ $paginator->previousPageUrl() }}">Previous</a>
        <a href="{{ $paginator->nextPageUrl() }}">Next</a>
        <br>
        {{ 'Page ' . $paginator->currentPage() . ' of ' . $paginator->lastPage() }}
        <br>
    @endif

    Showed {{ $paginator->count() . ' of ' . $paginator->total() . ' items' }}
    <br>

    Show per page
        <a href="{{ request()->fullUrlWithQuery(['limit' => 15]) }}">15</a>
        <a href="{{ request()->fullUrlWithQuery(['limit' => 50]) }}">50</a>
        <a href="{{ request()->fullUrlWithQuery(['limit' => 100]) }}">100</a>
</div>


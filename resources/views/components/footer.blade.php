<footer class="c-footer">
    <div class="container-xl">
        <div class="row">
            <div class="col py-5">
                <div class="d-flex column-gap-5 row-gap-4 flex-wrap justify-content-md-center small">
                    <span class="text-body-emphasis">&copy; {{ now()->year }} {{ config('app.name') }}</span>
                    <a href="{{ route('terms.terms') }}" class="link-body-emphasis">Algemene voorwaarden</a>
                    <a href="{{ route('terms.privacy') }}" class="link-body-emphasis">Privacy policy</a>
                    <a href="{{ route('terms.cookie') }}" class="link-body-emphasis">Cookie policy</a>
                </div>
            </div>
        </div>
    </div>
</footer>

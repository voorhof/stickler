<div class="c-cookie-banner js-cookie-banner py-3">
    <div class="container-xl">
        <div class="d-flex gap-2 flex-column flex-sm-row align-items-center px-md-3 px-xl-4 px-xxl-5">
            <div>
                <p class="small text-white mb-0">
                    {!! trans('cookie-consent::texts.message') !!}
                </p>
            </div>
            <div class="flex-shrink-0 ms-auto">
                <button class="btn btn-secondary btn-sm d-flex align-items-center gap-2">
                    <strong class="fw-medium">{{ trans('cookie-consent::texts.agree') }}</strong>
                    <div class="fs-5"><x-icons.cookie /></div>
                </button>
            </div>
        </div>
    </div>
</div>

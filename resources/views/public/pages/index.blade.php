<x-layouts.app>
    <section class="py-5">
        <div class="container-xl">
            <div class="row justify-content-center">
                <div class="col-auto">
                    <h1>
                        @lang('Pages')
                    </h1>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-auto">
                    <ol>
                        @foreach($pages as $page)
                            <li>
                                <a href="{{ route('pages.show', $page) }}">
                                    {{ $page->title }}
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>

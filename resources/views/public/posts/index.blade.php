<x-layouts.app>
    <section class="py-5">
        <div class="container-xl">
            <div class="row justify-content-center">
                <div class="col-auto">
                    <h1>
                        @lang('Blogposts')
                    </h1>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-auto">
                    <ol>
                        @foreach($posts as $post)
                            <li>
                                <a href="{{ route('posts.show', $post) }}">
                                    {{ $post->title }}
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>

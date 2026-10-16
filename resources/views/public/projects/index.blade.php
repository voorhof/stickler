<x-layouts.app>
    <section class="py-5">
        <div class="container-xl">
            <div class="row justify-content-center">
                <div class="col-auto">
                    <h1>
                        @lang('Projects')
                    </h1>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-auto">
                    <ol>
                        @foreach($projects as $project)
                            <li>
                                <a href="{{ route('projects.show', $project) }}">
                                    {{ $project->title }}
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>

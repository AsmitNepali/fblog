<x-blog-layout>
    <section></section>
    <section class="pt-8 pb-16">
        <div class="container mx-auto">
            <div class="grid gap-x-20 gap-y-10 sm:grid-cols-3">
                <div class="sm:col-span-2">
                    <div class="flex flex-col justify-end">
                        <div class="rounded-xl h-full overflow-hidden bg-slate-200 w-full">
                            <img class="h-full object-cover object-top" src="{{ asset($post->cover_photo_path) }}"
                                 alt="{{ $post->photo_alt_text }}">
                                {{ $post->categories }}
                        </div>
                        <div class="py-4 flex items-center justify-between gap-x-3 mb-5">
{{--                            <ul class="flex gap-x-2">--}}
{{--                                <li class="bg-slate-200 rounded-full h-10 w-10"></li>--}}
{{--                                <li class="bg-slate-200 rounded-full h-10 w-10"></li>--}}
{{--                                <li class="bg-slate-200 rounded-full h-10 w-10"></li>--}}
{{--                                <li class="bg-slate-200 rounded-full h-10 w-10"></li>--}}
{{--                            </ul>--}}
                            <div>
                                    <span class="block text-slate-500 text-sm font-medium">
                                        {{ $post->user->name }} • {{ $post->formattedPublishedDate() }}
                                    </span>
                            </div>
                        </div>
                        <div>
                            <h1 class="text-2xl mb-5 font-semibold">
                                {{ $post->title }}
                            </h1>
                            <div>
                                <article class="m-auto mt-12 leading-6">
                                   {!! $post->body !!}
                                </article>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="mb-10">
                        <div class="relative items-center flex gap-x-8 mb-2">
                            <h2 class="whitespace-nowrap text-2xl font-semibold">
                                Recent Post
                            </h2>
                            <div class="flex w-full items-center">
                                <span class="h-0.5 w-full rounded-full bg-slate-200"></span>
                            </div>
                        </div>
                        <div class="flex flex-col divide-y">
                            <x-blog-recent-post/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-blog-layout>
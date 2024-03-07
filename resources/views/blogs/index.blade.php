<x-blog-layout>
    <section></section>
    <section class="pt-8 pb-16">
        <div class="container mx-auto">
            <div class="grid grid-cols-2 gap-x-10">
            {{--      Hero Post      --}}
                <x-blog-hero-post />
            {{--      Hero Post      --}}
                <div class="flex flex-col gap-y-5">
                    <x-blog-hero-child-post />
                </div>
            </div>
        </div>
    </section>
    <section class="pt-8 pb-16">
        <div class="container mx-auto">
            <div class="relative items-center flex gap-x-8 mb-6">
                <h2 class="whitespace-nowrap text-2xl font-semibold">
                    All Blog Posts
                </h2>
                <div class="flex w-full items-center">
                    <span class="h-0.5 w-full rounded-full bg-slate-200"></span>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-y-14 gap-x-12">
            @foreach($posts as $post)
                <div>
                    <div class="flex flex-col gap-y-5">
                        <div class="rounded h-[200px] bg-slate-200 w-full">
                            <img class="h-full w-full object-cover object-top"
                                 src="{{ asset($post->cover_photo_path) }}"
                                 alt="post-featured-image">
                        </div>
                        <div class="space-y-3">
                            <a href="{{ route('post.show', ['post' => $post->slug]) }}" class="text-xl mb-2 font-semibold hover:text-blue-600">
                                {{ $post->title }}
                            </a>
                            <p class="mb-3">
                               {{ Str::limit($post->sub_title, 100) }}
                            </p>
                            <span class="mb-2 block text-slate-500 text-sm font-medium">{{$post->user->name}} • {{$post->published_at?->format('d M Y')}}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

</x-blog-layout>


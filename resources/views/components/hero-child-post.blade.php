@foreach($posts as $post)
    <div class="flex gap-x-8">
        <div class="rounded-xl overflow-hidden min-w-[300px] min-h-[200px] bg-slate-200 w-full">
            <img class="h-full w-full object-cover object-top"
                 src="{{ asset($post->cover_photo_path) }}"
                 alt="post-featured-image">
        </div>
        <div class="py-4 flex flex-col gap-y-2">
            <div>
                <span class="px-3 py-1 border text-xs font-semibold rounded-full">Blog</span>
            </div>
            <a href="{{ route('post.show', ['post' => $post->slug]) }}" class="text-lg mb-2 font-semibold hover:text-blue-600">
                {{ $post->title }}
            </a>
            <span class="mb-2 block text-slate-500 text-sm font-medium">{{ $post->user->name }} • {{ $post->published_at?->diffForHumans() }}</span>
        </div>
    </div>
@endforeach
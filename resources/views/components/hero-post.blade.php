@props(['post'=> $post])
<div>
    <div class="flex flex-col gap-y-5">
        <div class="rounded-xl overflow-hidden h-[250px] bg-slate-200 w-full">
            <img class="h-full w-full object-cover object-top"
                 src="{{ asset($post->cover_photo_path) }}"
                 alt="{{ $post->photo_alt_text }}">
        </div>
        <div class="space-y-3">
            <a href="{{ route('post.show', ['post' => $post->slug]) }}" class="text-xl mb-2 font-semibold hover:text-blue-600">
                {{ $post->title }}
            </a>
            <p class="mb-3">
                {!! Str::limit($post->sub_title) !!}
            </p>
            <span class="mb-2 block text-slate-500 text-sm font-medium">{{ $post->user->name }} • {{ $post->bulished_at }}</span>
        </div>
    </div>
</div>
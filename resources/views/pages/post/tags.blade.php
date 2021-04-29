<div class="decoration">
    @foreach($tags as $tag)
        <a href="#" class="btn btn-default">{{ $tag->title }}</a>
    @endforeach
</div>

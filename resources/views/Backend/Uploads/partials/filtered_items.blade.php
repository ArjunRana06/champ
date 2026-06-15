<div class="filtered-list-container">
    @forelse($activities as $activity)
        <div class="filtered-item">
            <div class="filtered-item-meta">
                <span class="filtered-badge">{{ ucfirst($activity->type) }}</span>
                <span class="filtered-time">{{ $activity->created_at->diffForHumans() }}</span>
            </div>
            <div class="filtered-item-content">
                @if(in_array($activity->type, ['text', 'emoji', 'emotion']))
                    <p>{{ $activity->parsed_content }}</p>
                @elseif($activity->type === 'image' && $activity->file_path)
                    <img src="{{ $activity->file_url }}" class="filtered-media">
                @elseif($activity->type === 'video' && $activity->file_path)
                    <video controls class="filtered-media">
                        <source src="{{ $activity->file_url }}" type="video/mp4">
                    </video>
                @endif
            </div>
        </div>
    @empty
        <p class="filtered-empty">No {{ $type }} found.</p>
    @endforelse
</div>

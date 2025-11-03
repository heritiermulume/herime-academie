<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PlyrPlayer extends Component
{
    public $lesson;
    public $course;
    public $isMobile;
    public $videoId;
    public $playerId;
    
    /**
     * Create a new component instance.
     */
    public function __construct($lesson, $course, $isMobile = false)
    {
        $this->lesson = $lesson;
        $this->course = $course;
        $this->isMobile = $isMobile;
        $this->videoId = $lesson->youtube_video_id ?? '';
        $this->playerId = ($isMobile ? 'plyr-mobile-' : 'plyr-player-') . ($lesson->id ?? 'preview');
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.plyr-player');
    }
}

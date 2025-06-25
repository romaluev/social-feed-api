<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'hotness' => (float) $this->hotness,
            'view_count' => $this->view_count,
            'created_at' => $this->created_at->toISOString(),
            'author' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'excerpt' => $this->getExcerpt(),
            'reading_time' => $this->getReadingTime(),
            'is_hot' => $this->hotness > 70,
        ];
    }

    private function getExcerpt(int $length = 200): string
    {
        $content = strip_tags($this->content);
        return strlen($content) > $length
            ? substr($content, 0, $length) . '...'
            : $content;
    }

    private function getReadingTime(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, (int) ceil($wordCount / 200));
    }
}

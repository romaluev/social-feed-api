<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\ResourceCollection;
use App\Http\Resources\PostResource;

class PostCollection extends ResourceCollection
{
    public $collects = PostResource::class;
}

<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Video;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VideoController extends BasicCrudController
{
    private $rules = [];

    public function __construct()
    {
        // TODO
    }

    protected function model()
    {
        return Video::class;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }
}

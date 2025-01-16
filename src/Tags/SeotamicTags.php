<?php

namespace Cnj\Seotamic\Tags;

use Illuminate\Support\Arr;

class SeotamicTags extends Tags
{
    /**
     * @var string
     */
    protected static $handle = 'seotamic';

    /**
     * Returns the whole set of tags
     *
     * @return string
     */
    public function index()
    {
        return view('seotamic::all', $this->values());
    }

    /**
     * Returns only the requested tag as a string.
     *
     * This is useful when you want to use the tag in a template.
     * If the tag does not exist, it returns null.
     *
     * @return string | null
     */
    public function wildcard()
    {
        return Arr::get($this->values(), str_replace(":", ".", $this->method));
    }

    /**
     * Outputs only the Open Graph Tags
     */
    public function og()
    {
        return view('seotamic::partials._og', $this->values());
    }

    /**
     * Outputs only the Twitter tags
     */
    public function twitter()
    {
        return view('seotamic::partials._twitter', $this->values());
    }

    protected function values(): array
    {
        return [
            'meta' => $this->context->value('seotamic_meta'),
            'social' => $this->context->value('seotamic_social')
        ];
    }
}

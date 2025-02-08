<?php

namespace App\Service;

use Symfony\Component\String\Slugger\SluggerInterface;

class SlugService
{
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function generateSlug(string $text): string
    {
        return $this->slugger->slug($text)->lower();
    }
}

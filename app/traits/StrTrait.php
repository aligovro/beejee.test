<?php

namespace app\traits;

trait StrTrait
{
    public function setHtmlspecialchars($text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
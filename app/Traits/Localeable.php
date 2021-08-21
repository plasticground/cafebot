<?php


namespace App\Traits;

/**
 * Trait Localeable
 * @package App\Traits
 * @property string $ru_name
 * @property string $ua_name
 */
trait Localeable
{
    /**
     * @param string $lang
     * @return string
     */
    public function getName(string $lang = 'ua'): string
    {
        return $lang === 'ua' ? $this->ua_name : $this->ru_name;
    }
}

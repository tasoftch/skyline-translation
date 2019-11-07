<?php
/**
 * Copyright (c) 2018 TASoft Applications, Th. Abplanalp <info@tasoft.ch>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Skyline\Translation\Locale;
use Skyline\Translation\Exception\BadLocaleException;

/**
 * Locale holds information about a region and the language.
 * @package Skyline\Translation
 */
class Locale implements LocaleInterface
{
    private $locale, $language, $region;

    /**
     * Pass a locale ID which is a string
     * @param string|LocaleInterface $locale
     * @throws BadLocaleException
     */
    public function __construct($locale) {
        if(preg_match("/^(.+?)(?:(?:_|-)(.+)|)$/i", ($locale instanceof LocaleInterface) ? $locale->getIdentifier() : (string) $locale, $ms)) {
            $this->locale = $ms[0];
            $this->language = strtolower($ms[1]);
            $this->region = isset($ms[2]) ? strtoupper($ms[2]) : NULL;
        } else
            throw new BadLocaleException("Can not recognize locale string '$locale'", 143);
    }

    /**
     * Returns the locale identification string
     * @return string
     */
    public function getIdentifier(): string { return $this->locale; }

    /**
     * Returns the language (de_CH => de)
     * @return string
     */
    public function getLanguage(): string { return $this->language; }

    /**
     * Returns the region (de_CH => CH)
     * @return string|null
     */
    public function getRegion(): ?string { return $this->region; }

    /**
     * Default stringify method returns the identifier string
     *
     * @return string
     */
    public function __toString() { return (string) $this->getIdentifier(); }
}
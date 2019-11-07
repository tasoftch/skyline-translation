<?php
/**
 * BSD 3-Clause License
 *
 * Copyright (c) 2019, TASoft Applications
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 *  Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 *  Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace Skyline\Translation\Locale;


use ArrayIterator;
use Countable;
use IteratorAggregate;
use Skyline\Translation\Exception\BadLocaleException;

/**
 * The LocaleCollection holds one or more locales and allows to compare, manage and order them.
 * @package Skyline\Translation
 */
class LocaleCollection implements IteratorAggregate, Countable
{
    private $locales = [], $byLang = [], $byRegion = [];

    const ORDERED_BY_LANGUAGE = '\\Skyline\\Translation\\Locale\\LocaleCollection::sortByLanguage';
    const ORDERED_BY_REGION = '\\Skyline\\Translation\\Locale\\LocaleCollection::sortByRegion';

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->locales);
    }

    public function count()
    {
        return count($this->locales);
    }


    /**
     * LocaleCollection constructor.
     * @param LocaleInterface[]|string[]|NULL $locales
     */
    public function __construct($locales = NULL) {
        if(is_string($locales) || $locales instanceof LocaleInterface) {
            $locales = func_get_args();
        }
        if(is_array($locales))
            $this->setLocales( $locales );
    }

    /**
     * Set a list of locales
     * @param LocaleInterface[]|string[] $locales
     */
    public function setLocales(array $locales) {
        $this->locales = [];
        foreach($locales as $loc)
            $this->addLocale($loc);
    }

    /**
     * Gets all supported locales
     * @return array
     */
    public function getLocales() {
        return array_values($this->locales);
    }

    /**
     * Gets all supported languages
     */
    public function getSupportedLanguages() {
        return array_keys($this->byLang);
    }

    /**
     * Gets all supported regions
     */
    public function getSupportedRegions() {
        return array_keys($this->byRegion);
    }

    /**
     * @param LocaleInterface|string $locale
     */
    public function addLocale($locale) {
        if(is_string($locale))
            $locale = new Locale($locale);

        if($locale instanceof LocaleInterface) {
            $this->locales[ $locale->getIdentifier() ] = $locale;

            // Default language (_) is always first element
            if(!isset($this->byLang[$locale->getLanguage()]))
                $this->byLang[ $locale->getLanguage() ] = ["_"=> NULL];

            $reg = $locale->getRegion() ?: '_';
            $this->byLang[ $locale->getLanguage() ][ $reg ] = $locale;
            if($locale->getRegion())
                $this->byRegion[ $reg ][$locale->getLanguage()] = $locale;
        } else
            throw new BadLocaleException("Can only add locales to collection");
    }

    /**
     * Removes all locales
     */
    public function removeAllLocales() {
        $this->locales = [];
        $this->byLang = [];
    }

    public function __debugInfo() {
        return array_keys($this->locales);
    }

    /**
     * Checks, if a given locale exists in the collection
     *
     * @param LocaleInterface|string $locale
     * @return bool
     */
    public function containsLocale($locale): bool {
        foreach($this->locales as $loc) {
            /** @var Locale $loc */
            if($loc->getIdentifier() == (string)$locale)
                return true;
        }
        return false;
    }

    /**
     * Returns all locales that supports the given language
     *
     * @param $language
     * @return LocaleInterface[]
     */
    public function getLocalesForLanguage($language) {
        return isset($this->byLang[$language]) ? array_values($this->byLang[$language]) : [];
    }

    /**
     * Returns all supported languages spoken in given region
     *
     * @param $region
     * @return string[]
     */
    public function getLanguagesForRegion($region) {
        return array_keys( $this->byRegion[ $region ] ?? []);
    }

    /**
     * Chooses the first available locale by its language code and its region if passed
     *
     * @param string $langCode
     * @param bool $regionCode
     * @return mixed|null|Locale
     */
    public function getLocaleForLanguage(string $langCode, $regionCode = false) {
        if($regionCode) {
            return $this->byLang[strtolower($langCode)][strtoupper($regionCode)] ?? NULL;
        } else {
            return $this->byLang[strtolower($langCode)]["_"] ?? NULL;
        }
    }

    /**
     * Chooses the first available locale by its region and its language code if passed
     *
     * @param string $region
     * @param bool $langCode
     * @return mixed|null|Locale
     */
    public function getLocaleForRegion(string $region, $langCode = false) {
        $region = strtolower($region);
        $langCode = strtolower($langCode);

        foreach($this->byRegion[ strtoupper($region) ] ?? [] as $locale) {
            /** @var Locale $locale */

            if(strtolower($locale->getRegion()) == $region && (!$langCode || strtolower($locale->getLanguage()) == $langCode))
                return $locale;
        }
        return NULL;
    }


    /**
     * @param string|LocaleInterface $locale
     * @return LocaleInterface|null
     */
    public function pickBestLocale($locale): ?LocaleInterface {
        if(is_string($locale))
            $locale = new Locale($locale);

        if($locales = $this->byLang[ $locale->getLanguage() ] ?? NULL) {
            return $locales[ $locale->getRegion() ?: "_" ] ?? $locales["_"] ?? (count($locales)>1 ? array_values($locales)[1] : NULL);
        }
        return NULL;
    }

    /**
     * Makes a new collection with all locales existing in both collections
     *
     * @param LocaleCollection $collection
     * @param bool $includeStandardLocales      If set to true, the intersection collection will always have the standard locale (without region) if available.
     * @param bool $extendToRegion              If set to true, the intersection collection will contain a locale with region, even if no region was required
     * @return LocaleCollection
     */
    public function intersect(LocaleCollection $collection, bool $includeStandardLocales = true, bool $extendToRegion = true): LocaleCollection {
        $newCollection = new static();

        $add = function($locale) use ($newCollection) {
            if(!$newCollection->containsLocale($locale))
                $newCollection->addLocale($locale);
        };

        $langs = array_intersect(array_keys($this->byLang), $order = array_keys($collection->byLang));

        usort($langs, function($A, $B) use ($order) {
            $A = array_search($A, $order);
            $B = array_search($B, $order);

            return $A <=> $B;
        });

        foreach($langs as $langCode) {
            $colKeys = array_keys($collection->byLang[$langCode]);
            $locales = $this->byLang[$langCode];

            $order = array_keys($collection->byLang[$langCode]);
            uksort($locales, function($A, $B) use ($order) {
                if($A == '_')
                    return -1;
                $A = array_search($A, $order);
                $B = array_search($B, $order);

                return $A <=> $B;
            });

            foreach($locales as $regionCode => $locale) {
                if($locale) {
                    if($regionCode == '_' && $includeStandardLocales) {
                        $add($locale);
                        if(($idx = array_search("_", $colKeys)) !== false) {
                            unset($colKeys[$idx]);
                        }
                        continue;
                    }

                    if(($idx = array_search($regionCode, $colKeys)) !== false) {
                        $add($locale);
                        unset($colKeys[$idx]);
                    }
                }
            }

            if($extendToRegion && in_array("_", $colKeys)) {
                if(count($locales)>1)
                    $add( array_values($locales)[1] );
            }
        }

        return $newCollection;
    }

    /**
     * @param LocaleInterface $a
     * @param LocaleInterface $b
     * @return int
     */
    public static function sortByLanguage(LocaleInterface $a, LocaleInterface $b) {
        $A = $a->getRegion() !== NULL;
        $B = $b->getRegion() !== NULL;

        if($A)
            return 1;
        if($B)
            return -1;
        return 0;
    }

    /**
     * @param LocaleInterface $a
     * @param LocaleInterface $b
     * @return int
     */
    public static function sortByRegion(LocaleInterface $a, LocaleInterface $b) {
        $A = $a->getRegion() !== NULL;
        $B = $b->getRegion() !== NULL;

        if($A)
            return -1;
        if($B)
            return 1;
        return 0;
    }
}
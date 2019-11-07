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

namespace Skyline\Translation\Translation;

/**
 * The concat translation allows to join a list of items using specific concat strings:
 *
 * array()                          => empty string property
 * array("test")                    => "test"
 * array("one", "two")              => "one and two"
 *                                         ^^^^^ append string
 * array("one", "two", "three")     => "one, two and three"
 *                                         ^^ concat string
 * array("one", "two", ...)         => "one, two, three, ... and last
 *
 * @package Skyline\Translation\Translation
 */
class ConcatTranslation extends AbstractDependentTranslation
{
    /** @var string */
    private $emptyList = '';
    /** @var string */
    private $concatString = ', ';
    /** @var string */
    private $appendString = ' and ';

    /**
     * ConcatTranslation constructor.
     * @param string $emptyList
     * @param string $concatString
     * @param string $appendString
     */
    public function __construct(string $emptyList = '', string $concatString = ', ', string $appendString = ' and ')
    {
        $this->emptyList = $emptyList;
        $this->concatString = $concatString;
        $this->appendString = $appendString;
    }


    protected function applyTranslation($value)
    {
        if(is_array($value)) {
            switch (count($value)) {
                case 0: return $this->getEmptyList();
                case 1: return array_pop($value);
                case 2: return sprintf("%s%s%s", $value[0], $this->getAppendString(), $value[1]);
                default:
                    $last = array_pop($value);
                    return sprintf("%s%s%s", implode($this->getConcatString(), $value), $this->getAppendString(), $last);
            }
        } elseif(NULL === $value)
            return $this->getEmptyList();

        trigger_error("Concat translation requires an array to apply", E_USER_NOTICE);
        return "";
    }

    /**
     * @return string
     */
    public function getEmptyList(): string
    {
        return $this->emptyList;
    }

    /**
     * @return string
     */
    public function getConcatString(): string
    {
        return $this->concatString;
    }

    /**
     * @return string
     */
    public function getAppendString(): string
    {
        return $this->appendString;
    }

    /**
     * @param string $emptyList
     */
    public function setEmptyList(string $emptyList): void
    {
        $this->emptyList = $emptyList;
    }

    /**
     * @param string $concatString
     */
    public function setConcatString(string $concatString): void
    {
        $this->concatString = $concatString;
    }

    /**
     * @param string $appendString
     */
    public function setAppendString(string $appendString): void
    {
        $this->appendString = $appendString;
    }
}
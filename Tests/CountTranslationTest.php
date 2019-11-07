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

/**
 * CountTranslationTest.php
 * skyline-translation
 *
 * Created on 2019-11-07 16:32 by thomas
 */

use PHPUnit\Framework\TestCase;
use Skyline\Translation\Translation\CountTranslation;

class CountTranslationTest extends TestCase
{
    public function testCountStructure() {
        $translation = new CountTranslation();
        $translation->addCountTranslation(0, 'nothing');

        $this->assertEquals([
            0 => 'nothing'
        ], $translation->getCountTranslations());

        $translation->addCountTranslation(1, 'one');
        $translation->addCountTranslation(2, 'two');
        $translation->addCountTranslation(5, 'five');

        $this->assertEquals([
            0 => 'nothing',
            1 => 'one',
            2 => 'two',
            5 => 'five'
        ], $translation->getCountTranslations());

        $translation->addCountTranslation(10, 'ten');
        $translation->addCountTranslation(3, 'three');

        $translation->addCountTranslation(8, 'eight');

        $this->assertEquals([
            0 => 'nothing',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            5 => 'five',
            8 => 'eight',
            10 => 'ten',
        ], $translation->getCountTranslations());

        $this->assertEquals("nothing", $translation);

        $translation->apply(1);
        $this->assertEquals("one", $translation);

        $translation->apply(2);
        $this->assertEquals("two", $translation);

        $translation->apply(3);
        $this->assertEquals("three", $translation);

        $translation->apply(4);
        $this->assertEquals("three", $translation);

        $translation->apply(5);
        $this->assertEquals("five", $translation);

        $translation->apply(6);
        $this->assertEquals("five", $translation);

        $translation->apply(7);
        $this->assertEquals("five", $translation);

        $translation->apply(8);
        $this->assertEquals("eight", $translation);

        $translation->apply(9);
        $this->assertEquals("eight", $translation);

        $translation->apply(10);
        $this->assertEquals("ten", $translation);

        $translation->apply(11);
        $this->assertEquals("ten", $translation);

        $translation->apply(12);
        $this->assertEquals("ten", $translation);
    }
}

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
 * TranslationTest.php
 * skyline-translation
 *
 * Created on 16.09.18 14:12 by thomas
 */

namespace Skyline\Translation\Test;

use PHPUnit\Framework\TestCase;
use Skyline\Translation\Translation\ConcatTranslation;
use Skyline\Translation\Translation\CountTranslation;
use Skyline\Translation\TranslationManager;

class TranslationTest extends TestCase
{
    public function testSimpleTranslation() {
        $tm = new TranslationManager('de_AT');
        $tm->setClientLocales([
            'en',
            'de'
        ]);

        $translations = [
            "de_CH" => "Hallo Wält",
            "de" => "Hallo Welt",
            "de_AT" => "Moin",
            "en" => 'Hello World',
            "pt" => 'Ola mundo'
        ];

        $this->assertEquals("Hello World", $tm->translate($translations));

        $tm->setClientLocales(['pt_BR']);
        $this->assertEquals("Ola mundo", $tm->translate($translations));

        $tm->setClientLocales(['de_DE', 'de_CH']);
        $this->assertEquals("Hallo Wält", $tm->translate($translations));

        $tm->setClientLocales(['de_DE']);
        $this->assertEquals("Hallo Welt", $tm->translate($translations));

        $tm->setClientLocales(['jp']);
        $this->assertEquals("Moin", $tm->translate($translations));
    }

    /**
     * @expectedException \PHPUnit\Framework\Error\Notice
     */
    public function testNoDefaultTranslation() {
        $tm = new TranslationManager('de');
        $tm->setClientLocales([
            'en',
            'de'
        ]);

        $translations = [
            "de_CH" => "Hallo Wält",
         //   "de" => "Hallo Welt",
            "de_AT" => "Moin",
            "en" => 'Hello World',
            "pt" => 'Ola mundo'
        ];

        $tm->setClientLocales(['jp']);
        $tm->translate($translations);
    }

    public function testCountTranslation() {
        $tm = new TranslationManager('de');
        $tm->setClientLocales([
            'en',
        ]);

        $translations = [
            "en" => new CountTranslation([0 => 'nothing', 1 => 'One element of %s found', 2 => '&count; elements of %s found']),
            "de" => new CountTranslation([0 => 'nichts', 1 => 'Ein Element von %s wurde gefunden', 2 => '&count; Elemente von %s wurden gefunden']),
            "pt" => new CountTranslation([0 => 'nada', 1 => 'Um item de %s foi achado', 2 => '&count; itens de %s foram achados'])
        ];

        $this->assertEquals("13 elements of Test found", $tm->translateCount(13, $translations, "Test"));
        $tm->setClientLocales([
            'pt',
        ]);

        $this->assertEquals("Um item de Test foi achado", $tm->translateCount(1, $translations, "Test"));
        $tm->setClientLocales([
        ]);

        $this->assertEquals("8 Elemente von Test wurden gefunden", $tm->translateCount(8, $translations, "Test"));
    }

    public function testItemConcatTranslation() {
        $tm = new TranslationManager('de');
        $tm->setClientLocales([]);

        $translations = [
            "de" => new ConcatTranslation('leer', ', ', ' und '),
            'pt' => new ConcatTranslation('vazia', ' - ', ' mais ')
        ];

        $this->assertEquals("leer", $tm->translateItemList([], $translations));

        $tm->setClientLocales(["pt_BR"]);
        $this->assertEquals("um - dois mais tres", $tm->translateItemList(["um", "dois", "tres"], $translations));
    }
}

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
 * LocaleCollectionTest.php
 * skyline-translation
 *
 * Created on 2019-11-07 11:36 by thomas
 */

use PHPUnit\Framework\TestCase;
use Skyline\Translation\Locale\LocaleCollection;

class LocaleCollectionTest extends TestCase
{
    public function testCollection() {
        $lc = new LocaleCollection(["de", "fr", "it"]);
        $this->assertEquals(["de", "fr", "it"], $lc->getLocales());

        $de = new \Skyline\Translation\Locale\Locale("de");
        $fr = new \Skyline\Translation\Locale\Locale("fr");
        $it = new \Skyline\Translation\Locale\Locale("it");

        $lc = new LocaleCollection($de, $fr, $it);
        $this->assertSame([
            $de, $fr, $it
        ], $lc->getLocales());
    }

    public function testContains() {
        $lc = new LocaleCollection(["de", "fr", "it"]);

        $this->assertTrue($lc->containsLocale('fr'));
        $this->assertTrue($lc->containsLocale(new \Skyline\Translation\Locale\Locale("it")));

        $this->assertFalse($lc->containsLocale('pt'));
        $this->assertFalse($lc->containsLocale(new \Skyline\Translation\Locale\Locale("en")));
    }

    public function testLocalesForLanguage() {
        $lc = new LocaleCollection(["de", "de_CH", "it_CH"]);
        $this->assertEquals(["de", "de_CH"], $lc->getLocalesForLanguage('de'));

        $this->assertEquals("de", $lc->getLocaleForLanguage(new \Skyline\Translation\Locale\Locale("de")));
        $this->assertEquals("de_CH", $lc->getLocaleForLanguage("de", "CH"));

        $this->assertNull($lc->getLocaleForLanguage("de", "AT"));

        $this->assertEquals(["de", "it"], $lc->getSupportedLanguages());
        $this->assertEquals(["CH"], $lc->getSupportedRegions());
    }

    public function testLocalesForRegion() {
        $lc = new LocaleCollection(["de", "de_CH", "it_CH"]);
        $this->assertEquals(["de", "it"], $lc->getLanguagesForRegion('CH'));
        $this->assertEmpty($lc->getLanguagesForRegion("US"));

        $this->assertEquals("de_CH", $lc->getLocaleForRegion("CH"));
        $this->assertEquals("it_CH", $lc->getLocaleForRegion("CH", 'it'));
    }

    public function testPickBestLocale() {
        $lc = new LocaleCollection(["de", "de_CH", "it_CH", "en_UK", "en", "en_US"]);
        $this->assertEquals("de_CH", $lc->pickBestLocale("de_CH"));
        $this->assertEquals("de", $lc->pickBestLocale("de"));
        $this->assertEquals("it_CH", $lc->pickBestLocale("it"));
        $this->assertEquals("en", $lc->pickBestLocale("en"));
        $this->assertEquals("en_US", $lc->pickBestLocale("en_US"));
        $this->assertEquals("en", $lc->pickBestLocale("en_NR"));
    }

    public function testIntersection() {
        $lc = new LocaleCollection(["de", "de_CH", "it_CH", "en_UK", "en", "en_US", "de_AT"]);

        $this->assertEquals(
            ["it_CH", 'en', 'en_US'],
            $lc->intersect(
                new LocaleCollection("it", 'en_US')
            )->getLocales()
        );
        $this->assertEquals(
            ["de"],
            $lc->intersect(
                new LocaleCollection("de")
            )->getLocales()
        );
        $this->assertEquals(
            ["it_CH", 'en', 'en_US'],
            $lc->intersect(
                new LocaleCollection("it", 'en_US')
            )->getLocales()
        );
        $this->assertEquals(
            [],
            $lc->intersect(
                new LocaleCollection("jp", 'ru')
            )->getLocales()
        );
        $this->assertEquals(
            ["de", "de_AT", "de_CH"],
            $lc->intersect(
                new LocaleCollection("de_AT", 'de_CH')
            )->getLocales()
        );

        $this->assertEquals(
            ["de", "de_CH", "de_AT", 'en', 'en_US'],
            $lc->intersect(
                new LocaleCollection("de_CH", 'de', 'en', 'de_AT', 'pt_BR', 'pt', 'en_US')
            )->getLocales()
        );
    }
}

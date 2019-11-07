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
 * LocaleTest.php
 * skyline-translation
 *
 * Created on 2019-11-07 11:33 by thomas
 */

use PHPUnit\Framework\TestCase;

class LocaleTest extends TestCase
{
    public function testLocale() {
        $loc = new \Skyline\Translation\Locale\Locale("de");
        $this->assertEquals("de", $loc->getLanguage());
        $this->assertNull($loc->getRegion());

        $this->assertEquals("de", (string) $loc);

        $loc2 = new \Skyline\Translation\Locale\Locale($loc);
        $this->assertEquals($loc->getRegion(), $loc2->getRegion());
        $this->assertEquals($loc->getLanguage(), $loc2->getLanguage());
        $this->assertEquals($loc->getIdentifier(), $loc2->getIdentifier());
    }

    public function testLocaleWithRegion() {
        $loc = new \Skyline\Translation\Locale\Locale("de_CH");
        $this->assertEquals("CH", $loc->getRegion());

        $loc = new \Skyline\Translation\Locale\Locale("de-AT");
        $this->assertEquals("AT", $loc->getRegion());

        $loc = new \Skyline\Translation\Locale\Locale("pt-br");
        $this->assertEquals("BR", $loc->getRegion());
    }
}

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

namespace Skyline\Translation\Service;

use Skyline\Translation\RegisterTranslationManager;
use Skyline\Translation\TranslationManager;
use Symfony\Component\HttpFoundation\Request;
use TASoft\Service\ConfigurableTrait;
use TASoft\Service\Container\AbstractContainer;

class TranslationManagerFactory extends AbstractContainer
{
	use ConfigurableTrait;

	const CONFIG_REQUEST = 'request';
	const CONFIG_LOCALE_PROVIDER = 'l-provider';
	const CONFIG_REGISTER_FILE = 'r-file';
	const CONFIG_SHOULD_REGISTER = 'register-enabled';

	private $defaultLocale;

	public function __construct($defaultLocale)
	{
		$this->defaultLocale = $defaultLocale;
	}

	protected function loadInstance()
	{
		$lpro = $this->getConfiguration()[static::CONFIG_LOCALE_PROVIDER] ?? NULL;

		if($this->getConfiguration()[static::CONFIG_SHOULD_REGISTER] ?? false) {
			$register = $this->getConfiguration()[static::CONFIG_REGISTER_FILE];
		} else
			$register = NULL;

		if($register)
			$tl = new RegisterTranslationManager($this->defaultLocale, $lpro, $register);
		else
			$tl = new TranslationManager($this->defaultLocale, $lpro);

		if($request = $this->getConfiguration()[ static::CONFIG_REQUEST ] ?? NULL) {
			if($request instanceof Request) {
				$languages = $request->getLanguages();
				$tl->setClientLocales( $languages );
			}
		}

		return $tl;
	}
}
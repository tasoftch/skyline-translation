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

namespace Skyline\Translation\Provider;

class CompiledTableProvider implements LocaleProviderInterface
{
	private $compiledFileName;
	private $fileContents;

	/**
	 * CompiledTableProvider constructor.
	 * @param $compiledFileName
	 */
	public function __construct($compiledFileName)
	{
		$this->compiledFileName = $compiledFileName;
	}

	private function _loadFile() {
		if(NULL === $this->fileContents) {
			$this->fileContents = require $this->compiledFileName;
		}

		return $this->fileContents;
	}


	/**
	 * @inheritDoc
	 */
	public function getSupportedLocaleNames(): array
	{
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function getLocalizations(string $key, string $table): array
	{
		if(isset($this->_loadFile()[$table])) {
			$tableContents = &$this->fileContents[$table];

			foreach($tableContents as $lid => &$translations) {
				if(is_string($translations))
					$translations = require $translations;
			}

			return array_filter(array_map(function($tr) use ($key) {
				foreach($tr as $k => $t) {
					if($k == $key)
						return $t;
				}
				return NULL;
			}, $tableContents));
		}

		return [];
	}
}
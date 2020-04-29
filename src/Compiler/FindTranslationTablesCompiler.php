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

namespace Skyline\Translation\Compiler;


use DirectoryIterator;
use Skyline\Compiler\AbstractCompiler;
use Skyline\Compiler\CompilerContext;
use Skyline\Compiler\Context\Code\SourceFile;
use Skyline\Compiler\Project\Attribute\SearchPathAttribute;

class FindTranslationTablesCompiler extends AbstractCompiler
{
	/**
	 * @inheritDoc
	 */
	public function compile(CompilerContext $context)
	{
		/** @var SourceFile $file */

		$translationTables = [];
		$languagePlainTables = [];

		$handleFile = function($fileName, $absPath) use (&$translationTables, &$languagePlainTables, $context) {
			if(preg_match("/^([a-zA-Z0-9_\-]+)\.(?:([a-z]+)[\-_]([A-Z]+)|([a-z]+))\.loc\.php$/",$fileName, $ms)) {
				@ list(, $tableName, $language, $region, $lngOnly) = $ms;

				$absPath = $context->getRelativeProjectPath( $absPath );

				if($lngOnly) {
					$languagePlainTables[$tableName][$lngOnly] = $absPath;
					$translationTables[$tableName][$lngOnly] = $absPath;
					return;
				}

				$locale = "{$language}_$region";
				if(!isset($translationTables[$tableName][$locale]) && isset($languagePlainTables[$tableName][$language])) {
					$translationTables[$tableName][$locale][] = $languagePlainTables[$tableName][$language];
				}

				$translationTables[$tableName][$locale][] = $absPath;
			} else
				trigger_error("Can not parse filename $fileName", E_USER_NOTICE);
		};

		foreach($context->getSourceCodeManager()->yieldSourceFiles("/\.loc\.php$/i", [ SearchPathAttribute::SEARCH_PATH_VENDOR ]) as $file) {
			$handleFile($file->getFileName(), $file->getRealPath());
		}

		if(is_dir($tdir = $context->getSkylineAppDataDirectory() . DIRECTORY_SEPARATOR . "/Translations")) {
			foreach(new DirectoryIterator($tdir) as $file) {
				if($file->getBasename()[0] == '.')
					continue;
				if(fnmatch("*.loc.php", $file->getBasename()))
					$handleFile($file->getBasename(), $file->getRealPath());
			}
		}

		$cdir = $context->getSkylineAppDataDirectory() . DIRECTORY_SEPARATOR . "Compiled/translations.php";
		file_put_contents($cdir, "<?php\nreturn " . var_export($translationTables, true) . ";");
	}
}
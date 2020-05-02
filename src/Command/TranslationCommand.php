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

namespace Skyline\Translation\Command;


use Skyline\CLI\AbstractSkylineCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TranslationCommand extends AbstractSkylineCommand
{
	protected function configure()
	{
		parent::configure();
		$this->setName("translate");
		$this->setDescription("Searches for translation instructions in all *.phtml templates");

		$this->addOption("export", 'e', InputOption::VALUE_REQUIRED, 'Exports any table translations into a given file.');
		$this->addOption("exclude", 'x', InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, 'List glob patterns for files or directories to exclude from scan');

		$this->addArgument("source-directory", InputArgument::OPTIONAL, 'Searches from this source', './');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$outputStyle = new OutputFormatterStyle('green');
		$this->io->getFormatter()->setStyle('file', $outputStyle);
		$this->io->getFormatter()->setStyle('translation', new OutputFormatterStyle("yellow", 'black'));

		$this->io->getFormatter()->setStyle('ok', new OutputFormatterStyle("green", NULL, ['bold']));

		$src = $input->getArgument("source-directory");

		$translations = [];
		if($export = $input->getOption("export")) {
			$exportHistory = [];
		}

		$transHandler = function($firstArg, $line) use (&$translations, &$file, &$exportHistory) {

			$trFile = NULL;
			foreach($translations as $translation) {
				if(in_array($firstArg, $translation[4])) {
					$trFile[] = $translation[3];
				}
			}

			if(is_array($exportHistory)) {
				$exportHistory[$firstArg][] = [$file, $line];
			}

			if($trFile)
				$trFile = sprintf("Translated in %d file(s): <ok>%s</ok>", count($trFile), implode(", ", $trFile));
			else
				$trFile = "<error>Not yet translated.</error>";

			$this->io->writeln("    Line $line: <translation>$firstArg</translation> => $trFile");
		};

		/** @var \SplFileInfo $file */
		$files = [];

		$excluded = $input->getOption("exclude") ?: [];

		$iterator = function($directory) use ($excluded, &$iterator) {
			/** @var \SplFileInfo $item */
			foreach(new \DirectoryIterator($directory instanceof \SplFileInfo ? $directory->getPathname() : $directory) as $item) {
				if($item->getBasename()[0] == '.')
					continue;

				foreach($excluded as $ex) {
					if(fnmatch($ex, $item->getBasename()))
						continue 2;
				}

				if($item->isDir())
					yield from $iterator($item);
				else
					yield $item;
			}
		};

		if(is_dir($src)) {
			foreach($iterator($src) as $file) {
				if(fnmatch("*.phtml", $file->getBasename())) {
					$files[] = $file->getPathname();
					continue;
				}

				if(preg_match("/^([^\.]+)\.([a-z_]+)\.loc\.php$/i", $file->getBasename(), $ms)) {
					$ms[] = $file->getPathname();
					$ms[] = array_keys( @include $file->getPathname());
					$translations[] = $ms;
				}
			}
		} elseif (is_file($src)) {
			$files[] = $src;
		}


		foreach($files as $file) {
			$this->io->writeln("File: <file>".$file."</file>");

			try {

				$tokens = token_get_all(file_get_contents( $file, TOKEN_PARSE ));
				$state = 0;

				foreach($tokens as $token) {
					if(is_array($token)) {
						if($token[0] == T_WHITESPACE || $token[0] == T_COMMENT || $token[0] == T_DOC_COMMENT)
							continue;

						if($state == 0) {
							if($token[0] == T_VARIABLE && strcasecmp($token[1], '$this') === 0) {
								$state = 1;
								continue;
							}
						}

						if($state == 1) {
							if($token[0] == T_OBJECT_OPERATOR) {
								$state = 2;
								continue;
							} else
								$state = 0;
						}

						if($state == 2) {
							if($token[0] == T_STRING) {
								if(strcasecmp($token[1], 'translate') === 0) {
									$state = 3;
									continue;
								}
							} else
								$state = 0;
						}

						if($state == 4) {
							if($token[0] == T_ENCAPSED_AND_WHITESPACE || $token[0] == T_CONSTANT_ENCAPSED_STRING) {
								$key = trim($token[1], "\"'");
								$transHandler($key, $token[2]);
							} else
								$state = 0;
						}
					} elseif($state == 3 && $token = "(")
						$state = 4;
					else
						$state = 0;
				}

				//$cb->call($translator);
			} catch (\Throwable $exception) {
				$this->io->writeln("<error> ** Error: " . $exception->getMessage() . "</error>");
			}
		}

		if($exportHistory) {
			$content = "<?php\n";
			$content .= "/**\n * Generated by Skyline CMS Binary :: Translation command.\n */\n\nreturn [\n";

			foreach($exportHistory as $key => $files) {
				foreach($files as $file) {
					list($f, $line) = $file;
					$content .= "\t// $f:$line\n";
				}
				$key = var_export($key, true);
				$content .= "\t$key => $key,\n\n";
			}

			$content .= "];";
			file_put_contents($export, $content);
		}

		return 0;
	}
}
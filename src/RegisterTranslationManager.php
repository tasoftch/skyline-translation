<?php

namespace Skyline\Translation;


class RegisterTranslationManager extends TranslationManager
{
	private $register, $_reg;
	
	public function __construct(string $defaultLocale, $supportedLocales = NULL, string $registeredTranslationsFile = NULL)
	{
		parent::__construct($defaultLocale, $supportedLocales);

		if($registeredTranslationsFile) {
			$this->register = $registeredTranslationsFile;
			if(is_file($registeredTranslationsFile))
				$this->_reg = require $registeredTranslationsFile;
		}
	}

	public function __destruct()
	{
		if($this->register && $this->_reg) {
			$data = var_export($this->_reg, true);
			file_put_contents($this->register, "<?php\nreturn $data;");
		}
	}
	
	public function translateGlobal(string $key, string $table = NULL, ...$arguments): string
	{
		if($this->register) {
			$traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			foreach($traces as $trace) {
				if($file = $trace['file'] ?? NULL) {
					// Forwarder method of DefaultRenderContext
					if($file !== __FILE__ && @$trace["function"] != 'call_user_func_array') {
						$ff = explode(getcwd()."/", $file);
						$file = end($ff);

						$file = "$file:{$trace['line']}";

						$table = $this->getDefaultGlobalTableName() ?? 'general';

						if(!in_array($file, $this->_reg[$table][$key] ?? []))
							$this->_reg[$table][$key][] = $file;
						break;
					}
				}
			}
		}
		return parent::translateGlobal($key, $table, $arguments);
	}
}
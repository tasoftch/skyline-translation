<?php
/**
 * Copyright (c) 2018 TASoft Applications, Th. Abplanalp <info@tasoft.ch>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Skyline\Translation;

use Exception;
use Skyline\Translation\Exception\BadLocaleException;
use Skyline\Translation\Locale\Locale;
use Skyline\Translation\Locale\LocaleCollection;
use Skyline\Translation\Locale\LocaleInterface;
use Skyline\Translation\Provider\LocaleProviderInterface;
use Skyline\Translation\Translation\DependentTranslationInterface;
use Skyline\Translation\Translation\TranslationInterface;
use Symfony\Component\HttpFoundation\Request;
use TASoft\Service\AbstractService;

class TranslationManager extends AbstractService
{
	const SERVICE_NAME = "translationManager";

    /**
     *
     * @var Locale;
     */
    private $defaultLocale;

    /**
     * @var array;
     * @internal
     */
    private $supportedLocaleStrings;

    /**
     * @var LocaleCollection
     */
    private $clientLocales = [];
    /**
     * @var LocaleCollection
     */
    private $serverLocales;

    /** @var LocaleProviderInterface|null  */
    private $tableProvider;

    /** @var string|null */
    private $defaultGlobalTableName;



    public function __construct(string $defaultLocale, $supportedLocales = NULL)
    {
        if($defaultLocale) {
            $this->defaultLocale = new Locale($defaultLocale);
        }
        if(is_array($supportedLocales))
            $this->supportedLocaleStrings = $supportedLocales;
        elseif($supportedLocales instanceof LocaleProviderInterface) {
            $this->tableProvider = $supportedLocales;
            $this->supportedLocaleStrings = $supportedLocales->getSupportedLocaleNames();
        } else {
            if($supportedLocales)
                throw new BadLocaleException("Supported locales must be an array or a locale provider");
        }
    }

	/**
     * @return LocaleInterface
     */
    public function getDefaultLocale(): LocaleInterface
    {
        return $this->defaultLocale;
    }

    /**
     * Get the client desired locales.
     * @return Locale[]|NULL
     */
    public function getClientLocales()
    {
        return $this->clientLocales ? $this->clientLocales->getLocales() : [];
    }

    /**
     * Sets the client locales. You may pass Locale objects or string values.
     * @param \Traversable|array $clientLocales
     * @throws BadLocaleException
     */
    public function setClientLocales($clientLocales): void
    {
        if(!$this->clientLocales)
            $this->clientLocales = new LocaleCollection();

        $this->clientLocales->removeAllLocales();

        foreach ($clientLocales as $locale) {
            if(is_string($locale)) {
                $locale = new Locale($locale);
            }

            if($locale instanceof Locale) {
                $this->clientLocales->addLocale($locale);
            }
        }
    }

    /**
     * Reads the client locales from the request
     * @param Request $request
     * @throws BadLocaleException
     */
    public function readClientLocalesFromRequest(Request $request) {
        $locales = [];

        foreach($request->getLanguages() as $client) {
            try {
                $locale = new Locale($client);
                $locales[] = $locale;
            } catch(Exception $e) {
                trigger_error($e->getMessage(), E_USER_NOTICE);
            }
        }

        $this->setClientLocales($locales);
    }

    /**
     * Returns all possible locales the server can deliver the render result
     * @return array
     */
    public function getServerLocales() {
        if(!isset($this->serverLocales)) {
            $this->serverLocales = new LocaleCollection();
            foreach($this->supportedLocaleStrings as $server) {
                try {
                    $locale = new Locale($server);
                    $this->serverLocales->addLocale( $locale );
                } catch(Exception $e) {
                    trigger_error($e->getMessage(), E_USER_NOTICE);
                }
            }
        }
        return $this->serverLocales->getLocales();
    }

	/**
	 * @return string|null
	 */
	public function getDefaultGlobalTableName(): ?string
	{
		return $this->defaultGlobalTableName;
	}

	/**
	 * @param string|null $defaultGlobalTableName
	 * @return static
	 */
	public function setDefaultGlobalTableName(?string $defaultGlobalTableName)
	{
		$this->defaultGlobalTableName = $defaultGlobalTableName;
		return $this;
	}

	/**
	 * @param string $key
	 * @param string $table
	 * @param mixed ...$arguments
	 * @return string
	 */
    public function translateGlobal(string $key, string $table = NULL, ...$arguments): string {
		if($this->tableProvider) {
			if(!$table)
				$table = $this->getDefaultGlobalTableName() ?? 'general';

			$translations = $this->tableProvider->getLocalizations($key, $table);
			return $translations ? $this->translate($translations, ...$arguments) : $key;
		} else
			trigger_error("No table provider is set", E_USER_NOTICE);
    	return $key;
	}

    /**
     * Chooses from a bunch of translations the best one or the default.
     *
     * @param string[]|TranslationInterface[]|string $translations     The array's keys are used as locale identifiers
     * @param mixed ...$arguments
     * @return string
     */
    public function translate($translations, ...$arguments): string {
    	if(is_string($translations)) {
			$table = array_shift($arguments);
			return $this->translateGlobal($translations, $table, ...$arguments);
		}

        if($translation = $this->findTranslation($translations)) {
            if($translation instanceof DependentTranslationInterface)
                $translation->apply($arguments);
            return vsprintf($translation, $arguments);
        }

        return "";
    }

    /**
     * Translates a text that needs to be different depending on an number.
     *
     * @param int $count
     * @param string[]|TranslationInterface[] $translations
     * @param mixed ...$arguments
     * @return string
     */
    public function translateCount(int $count, array $translations, ...$arguments): string {
        if($translation = $this->findTranslation($translations)) {
            if($translation instanceof DependentTranslationInterface)
                $translation->apply($count);
            return vsprintf($translation, $arguments);
        }
        return "";
    }

    /**
     * @param array $itemList
     * @param array $translations
     * @param callable|null $stringifier
     * @param mixed ...$arguments
     * @return string
     */
    public function translateItemList(array $itemList, array $translations, callable $stringifier = NULL, ...$arguments): string {
        if($translation = $this->findTranslation($translations)) {
            if($translation instanceof DependentTranslationInterface)
                $translation->apply(is_callable($stringifier) ? array_map($stringifier, $itemList) : $itemList);
            return vsprintf($translation, $arguments);
        }
        return "";
    }

    /**
     * Finds the best translation
     *
     * @param string[]|TranslationInterface[] $translations
     * @param LocaleCollection|null $collection
     * @return string|TranslationInterface|null
     */
    public function findTranslation(array $translations, LocaleCollection $collection = NULL) {
        if(NULL === $collection)
            $collection = $this->clientLocales;

        if($collection) {
            $lc = new LocaleCollection(array_keys($translations));

            if(count( $intersection = $lc->intersect( $this->clientLocales ) )) {
                $locales = $intersection->getLocales();
                usort($locales, LocaleCollection::ORDERED_BY_REGION);

                foreach($locales as $locale) {
                    return $translations[(string)$locale];
                }
            } elseif($msg = $translations[ $this->getDefaultLocale()->getIdentifier() ] ?? NULL) {
                return $msg;
            } else {
                trigger_error("No translation available", E_USER_NOTICE);
                return "";
            }
        }

        return NULL;
    }
}
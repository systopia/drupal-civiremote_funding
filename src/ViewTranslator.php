<?php

/*
 * Copyright (C) 2022 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Drupal\civiremote_funding;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\Translator\TranslatorInterface;
use Drupal\language\ConfigurableLanguageManagerInterface;

final class ViewTranslator {

  private const DEFAULT_TRANSLATABLE_KEYS = [
    'label',
    'title',
    'description',
    'text',
    'submit_button',
    'type_custom_true',
    'type_custom_false',
  ];

  private ConfigFactoryInterface $configFactory;

  private LanguageManagerInterface $languageManager;

  private TranslatorInterface $translator;

  /**
   * @phpstan-var array<string>
   */
  private array $translatableKeys;

  /**
   * @phpstan-param array<string> $translatableKeys
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    LanguageManagerInterface $languageManager,
    TranslatorInterface $translator,
    array $translatableKeys = self::DEFAULT_TRANSLATABLE_KEYS
  ) {
    $this->configFactory = $configFactory;
    $this->languageManager = $languageManager;
    $this->translator = $translator;
    $this->translatableKeys = $translatableKeys;
  }

  public function translateView(string $viewName): void {
    if (!$this->languageManager instanceof ConfigurableLanguageManagerInterface) {
      return;
    }

    $viewConfig = $this->configFactory->get($viewName);
    /** @var string $viewLangcode */
    $viewLangcode = $viewConfig->get('langcode') ?? 'en';
    $langcodes = $this->getLangcodes($viewLangcode);

    /** @phpstan-var array<string, \Drupal\language\Config\LanguageConfigOverride> $viewConfigOverrides */
    $viewConfigOverrides = [];
    foreach ($langcodes as $langcode) {
      $viewConfigOverrides[$langcode] = $this->languageManager->getLanguageConfigOverride($langcode, $viewName);
    }

    foreach ($this->getTranslatableStrings($viewConfig->getRawData()) as $viewStringKey => $string) {
      foreach ($langcodes as $langcode) {
        try {
          $translation = $this->translator->getStringTranslation($langcode, $string, '');
          if (FALSE !== $translation) {
            $viewConfigOverrides[$langcode]->set($viewStringKey, $translation);
          }
        }
        // @phpstan-ignore-next-line
        catch (\Error $e) {
          // @ignoreException
        }
      }
    }

    array_map(fn ($viewConfigOverride) => $viewConfigOverride->save(), $viewConfigOverrides);
  }

  public function translateViews(string $viewNamePrefix): void {
    foreach ($this->configFactory->listAll($viewNamePrefix) as $viewName) {
      $this->translateView($viewName);
    }
  }

  /**
   * @phpstan-param array<int|string, mixed> $data
   * @phpstan-param array<int|string> $parentKeys
   *
   * @phpstan-return iterable<string, string>
   */
  private function getTranslatableStrings(array $data, array $parentKeys = []): iterable {
    foreach ($data as $key => $value) {
      $keys = array_merge($parentKeys, [$key]);
      if (is_array($value)) {
        foreach ($this->getTranslatableStrings($value, $keys) as $stringKey => $string) {
          yield $stringKey => $string;
        }
      }
      elseif (is_string($value) && '' !== $value && $this->isTranslatableKey($key)) {
        yield implode('.', $keys) => $value;
      }
    }
  }

  /**
   * @phpstan-return array<string>
   */
  private function getLangcodes(string $excludedLangcode): array {
    return array_filter(
      array_keys($this->languageManager->getLanguages()),
      fn ($langcode) => $excludedLangcode !== $langcode
    );
  }

  /**
   * @param string|int $key
   */
  private function isTranslatableKey($key): bool {
    return in_array($key, $this->translatableKeys, TRUE);
  }

}

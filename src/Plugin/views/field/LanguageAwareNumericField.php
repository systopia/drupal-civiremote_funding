<?php

declare(strict_types=1);

/*
 * Copyright (C) 2024 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Drupal\civiremote_funding\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\PluralTranslatableMarkup;
use Drupal\views\Plugin\views\field\NumericField;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extends Drupal's numeric field by the option to format the value based on the
 * current language.
 *
 * @ingroup views_field_handlers
 *
 * @Drupal\views\Annotation\ViewsField("numeric")
 */
final class LanguageAwareNumericField extends NumericField {

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private LanguageManagerInterface $languageManager;

  /**
   * {@inheritdoc}
   *
   * @return static
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $pluginId,
    $pluginDefinition
  ) {
    /** @var \Drupal\Core\Language\LanguageManagerInterface $languageManager */
    $languageManager = $container->get('language_manager');

    return new static($configuration, $pluginId, $pluginDefinition, $languageManager);
  }

  public function __construct(
    array $configuration,
    string $pluginId,
    $pluginDefinition,
    LanguageManagerInterface $languageManager
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions(): array {
    $options = parent::defineOptions();

    $options['format_language_aware'] = ['default' => FALSE];
    $options['format_style'] = ['default' => \NumberFormatter::DECIMAL];
    $options['currency'] = ['default' => ''];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state): void {
    parent::buildOptionsForm($form, $form_state);

    if (!empty($this->definition['float'])) {
      $insertPos = array_search('decimal', array_keys($form), TRUE);
      if (FALSE === $insertPos) {
        throw new \RuntimeException('Field "decimal" not found');
      }

      $form = array_merge(
        array_slice($form, 0, $insertPos),
        [
          'format_language_aware' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Format language aware'),
            '#default_value' => $this->options['format_language_aware'],
            '#description' => $this->t('If checked, the current language will be used to format the value.'),
            '#weight' => $form['decimal']['#weight'] ?? NULL,
          ],
          'format_style' => [
            '#type' => 'select',
            '#title' => $this->t('Style'),
            '#default_value' => $this->options['format_style'],
            '#options' => [
              \NumberFormatter::DECIMAL => $this->t('Decimal'),
              \NumberFormatter::CURRENCY => $this->t('Currency'),
            ],
            '#states' => [
              'visible' => [
                ':input[name="options[format_language_aware]"]' => ['checked' => TRUE],
              ],
              'required' => [
                ':input[name="options[format_language_aware]"]' => ['checked' => TRUE],
              ],
            ],
          ],
          'currency' => [
            '#type' => 'textfield',
            '#title' => $this->t('Currency'),
            '#default_value' => $this->options['currency'],
            '#description' => $this->t('3-letter ISO 4217 currency code. (Replacement patterns are allowed.)'),
            '#states' => [
              'visible' => [
                ':input[name="options[format_language_aware]"]' => ['checked' => TRUE],
                ':input[name="options[format_style]"]' => ['value' => \NumberFormatter::CURRENCY],
              ],
              'required' => [
                ':input[name="options[format_language_aware]"]' => ['checked' => TRUE],
                ':input[name="options[format_style]"]' => ['value' => \NumberFormatter::CURRENCY],
              ],
            ],
          ],
        ],
        array_slice($form, $insertPos)
      );

      $form['decimal']['#states']['visible'] = [
        ':input[name="options[format_language_aware]"]' => ['checked' => FALSE],
      ];
      $form['separator']['#states']['visible'] = [
        ':input[name="options[format_language_aware]"]' => ['checked' => FALSE],
      ];
    }
  }

  public function submitOptionsForm(&$form, FormStateInterface $formState): void {
    /** @phpstan-var array<string, mixed> $options */
    $options = &$formState->getValue('options');
    $options['format_language_aware'] = (bool) $options['format_language_aware'];
    // @phpstan-ignore-next-line
    $options['format_style'] = (int) $options['format_style'];
    parent::submitOptionsForm($form, $formState);
  }

  /**
   * {@inheritdoc}
   */
  // phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
  public function render(ResultRow $values) {
  // phpcs:enable
    $value = $this->getValue($values);

    // Check to see if hiding should happen before adding prefix and suffix
    // and before rewriting.
    if ($this->options['hide_empty'] && empty($value) && ($value !== 0 || $this->options['empty_zero'])) {
      return '';
    }

    // After the hide_empty check NULL values should be treated as a 0 value.
    $value = $value ?? 0;

    if (!empty($this->options['set_precision'])) {
      $precision = $this->options['precision'];
    }
    elseif (($decimalPosition = strpos((string) $value, '.')) !== FALSE) {
      $precision = strlen((string) $value) - $decimalPosition - 1;
    }
    else {
      $precision = 0;
    }

    if (is_float($value)) {
      $value = round($value, $precision);
      if ((int) $value == $value) {
        $value = (int) $value;
      }
    }

    if ($this->options['empty_zero'] && $value === 0) {
      return '';
    }

    // If we should format as plural, take the (possibly) translated plural
    // setting and format with the current language.
    if (is_int($value) && !empty($this->options['format_plural'])) {
      $value = PluralTranslatableMarkup::createFromTranslatedString($value, $this->options['format_plural_string']);
    }
    else {
      if ($this->options['format_language_aware']) {
        $style = $this->options['format_style'];
        $numberFormatter = new \NumberFormatter($this->languageManager->getCurrentLanguage()->getId(), $style);
        if (\NumberFormatter::CURRENCY === $style) {
          $currency = $this->viewsTokenReplace($this->options['currency'], $this->getRenderTokens([]));
          $value = $numberFormatter->formatCurrency($value, $currency);
        }
        else {
          $value = $numberFormatter->format($value);
        }
      }
      else {
        $value = number_format($value, $precision, $this->options['decimal'], $this->options['separator']);
      }
    }

    return $this->sanitizeValue($this->options['prefix'], 'xss')
      . $this->sanitizeValue($value)
      . $this->sanitizeValue($this->options['suffix'], 'xss');
  }

}

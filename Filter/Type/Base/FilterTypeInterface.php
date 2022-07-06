<?php
/*
 * This file is part of the Austral Filter Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FilterBundle\Filter\Type\Base;

use Austral\FormBundle\Field\Base\FieldInterface;

/**
 * Austral Filter Type Interface.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @abstract
 */
interface FilterTypeInterface
{

  const CONDITION_EQUAL = "=";
  const CONDITION_LIKE = "LIKE";
  const CONDITION_IN = "IN";

  const CONDITION_SUP = ">";
  const CONDITION_SUP_EQUAL = ">=";

  const CONDITION_INF = "<";
  const CONDITION_INF_EQUAL = "<=";

  /**
   * Get options
   * @return array
   */
  public function getOptions(): array;

  /**
   * @param array $options
   *
   * @return FilterTypeInterface
   */
  public function setOptions(array $options): FilterTypeInterface;

  /**
   * Get fieldname
   * @return string
   */
  public function getFieldname(): string;

  /**
   * Get fieldname
   *
   * @param $fieldname
   *
   * @return FilterTypeInterface
   */
  public function setFieldname($fieldname): FilterTypeInterface;

  /**
   * @return FieldInterface|null
   */
  public function getFormField(): ?FieldInterface;

  /**
   * @param string|null $subkey
   *
   * @return string|null|array|float|int|bool
   */
  public function getValue(string $subkey = null);

  /**
   * @param mixed $value
   * @param string|null $subkey
   *
   * @return FilterTypeInterface
   */
  public function setValue($value, string $subkey = null): FilterTypeInterface;

  /**
   * @return string
   */
  public function getCondition(): string;

  /**
   * @return string
   */
  public function getChildrenConcat(): string;

  /**
   * @return string
   */
  public function getQueryAlias(): string;

  /**
   * @param string $alias
   *
   * @return FilterTypeInterface
   */
  public function setQueryAlias(string $alias): FilterTypeInterface;

  /**
   * @return string
   */
  public function getValueForQueryParameter(): string;

}
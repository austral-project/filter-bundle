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
 * @template T of object
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @internal
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
   * @return array<string, mixed>
   */
  public function getOptions(): array;

  /**
   * @param array<string, mixed> $options
   *
   * @return $this
   */
  public function setOptions(array $options): self;

  /**
   * Get fieldname
   * @return string
   */
  public function getFieldname(): string;

  /**
   * Get fieldname
   *
   * @param string $fieldname
   *
   * @return $this
   */
  public function setFieldname(string $fieldname): self;

  /**
   * @return FieldInterface<T>|null
   */
  public function getFormField(): ?FieldInterface;

  /**
   * @param string|null $subkey
   *
   * @return mixed
   */
  public function getValue(?string $subkey = null): mixed;

  /**
   * @param mixed $value
   * @param string|null $subkey
   *
   * @return $this
   */
  public function setValue(mixed $value, ?string $subkey = null): self;

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
   * @return string
   */
  public function getFieldnameQuery(): string;

  /**
   * @param string $alias
   *
   * @return $this
   */
  public function setQueryAlias(string $alias): FilterTypeInterface;

  /**
   * @return string
   */
  public function getValueForQueryParameter(): string;

  /**
   * @param string $fieldname
   *
   * @return $this
   */
  public function setFieldnameQuery(string $fieldname): self;

  /**
   * @return mixed
   */
  public function getRenderValue(): mixed;

}
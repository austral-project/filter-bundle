<?php
/*
 * This file is part of the Austral Filter Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FilterBundle\Filter\Type;

use Austral\FilterBundle\Filter\Type\Base\FilterType;
use Austral\FilterBundle\Filter\Type\Base\FilterTypeInterface;
use Austral\FormBundle\Field as FormField;

/**
 * Austral Filter String Type.
 * @template T of object
 *
 * @extends FilterType<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class DateType extends FilterType
{

  /**
   * @var string
   */
  protected string $formFieldDefault = FormField\DatePicker::class;

  /**
   * @var array<int|string, mixed>
   */
  protected array $formFieldsAccepted = array(
    FormField\DatePicker::class,
  );

  /**
   * @param string $fieldname
   * @param array<string, mixed> $options
   *
   * @return DateType<T>
   * @throws \Exception
   */
  public static function create(string $fieldname, array $options = array()): DateType
  {
    /** @var DateType<T> $element */
    $element = new self($fieldname, $options);
    return $element;
  }

  /**
   * TextField constructor.
   *
   * @param string $fieldname
   * @param array<string, mixed> $options
   *
   * @throws \Exception
   */
  public function __construct(string $fieldname, array $options = array())
  {
    if(!array_key_exists("query", $options) || !array_key_exists("condition", $options['query']))
    {
      $options['query']["condition"] = FilterTypeInterface::CONDITION_EQUAL;
    }
    parent::__construct($fieldname, $options);
    if(!$this->options['value']["render"])
    {
      $this->options['value']["render"] = function($value) {
        return $value instanceof \DateTime ? $value->format("Y-m-d") : $value;
      };
    }
  }

  /**
   * @return string
   */
  public function getValueForQueryParameter(): string
  {
    return $this->getParameterFormat() ? sprintf($this->getParameterFormat(), $this->value ? $this->value->format("Y-m-d") : "") : "";
  }

}
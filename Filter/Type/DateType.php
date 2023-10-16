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
   * @var array|string[]
   */
  protected array $formFieldsAccepted = array(
    FormField\DatePicker::class,
  );

  /**
   * @param $fieldname
   * @param array $options
   *
   * @return $this
   * @throws \Exception
   */
  public static function create($fieldname, array $options = array()): DateType
  {
    return new self($fieldname, $options);
  }

  /**
   * TextField constructor.
   *
   * @param string $fieldname
   * @param array $options
   *
   * @throws \Exception
   */
  public function __construct($fieldname, array $options = array())
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
    return sprintf($this->getParameterFormat(), $this->value ? $this->value->format("Y-m-d") : "");
  }

}
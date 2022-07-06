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
use Austral\FormBundle\Field\Base\FieldInterface;

/**
 * Austral Filter String Type.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class StringChoiceType extends FilterType
{

  /**
   * @var string
   */
  protected string $formFieldDefault = FormField\ChoiceField::class;

  /**
   * @var array|string
   */
  protected $choiceValues;

  /**
   * @var array|string[]
   */
  protected array $formFieldsAccepted = array(
    FormField\ChoiceField::class,
    FormField\EntityField::class,
    FormField\SelectField::class
  );

  /**
   * @param $fieldname
   * @param $choiceValues
   * @param array $options
   *
   * @return StringChoiceType
   * @throws \Exception
   */
  public static function create($fieldname, $choiceValues, array $options = array()): StringChoiceType
  {
    return new self($fieldname, $choiceValues, $options);
  }

  /**
   * TextField constructor.
   *
   * @param string $fieldname
   * @param $choiceValues
   * @param array $options
   *
   * @throws \Exception
   */
  public function __construct($fieldname, $choiceValues, array $options = array())
  {
    if(!array_key_exists("query", $options) || !array_key_exists("condition", $options['query']))
    {
      $options['query']["condition"] = FilterTypeInterface::CONDITION_EQUAL;
    }
    $this->choiceValues = $choiceValues;
    parent::__construct($fieldname, $options);
  }

  /**
   * @param $formFieldClass
   * @param $fieldOptions
   *
   * @return FieldInterface
   */
  protected function createFormField($formFieldClass, $fieldOptions): FieldInterface
  {
    return new $formFieldClass($this->fieldname, $this->choiceValues, $fieldOptions);
  }

}
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
 * @template T of object
 *
 * @extends FilterType<T>
 *
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
   * @var mixed
   */
  protected mixed $choiceValues = null;

  /**
   * @var array<int|string, mixed>
   */
  protected array $formFieldsAccepted = array(
    FormField\ChoiceField::class,
    FormField\EntityField::class,
    FormField\SelectField::class
  );

  /**
   * @param string $fieldname
   * @param mixed $choiceValues
   * @param array<string, mixed> $options
   *
   * @return StringChoiceType<T>
   * @throws \Exception
   */
  public static function create(string $fieldname, mixed $choiceValues = null, array $options = array()): self
  {
    /** @var StringChoiceType<T> $element */
    $element = new self($fieldname, $choiceValues, $options);
    return $element;
  }

  /**
   * StringChoiceType constructor.
   *
   * @param string $fieldname
   * @param mixed $choiceValues
   * @param array<string, mixed> $options
   *
   * @throws \Exception
   */
  public function __construct(string $fieldname, mixed $choiceValues = null, array $options = array())
  {
    if(!array_key_exists("query", $options) || !array_key_exists("condition", $options['query']))
    {
      $options['query']["condition"] = FilterTypeInterface::CONDITION_EQUAL;
    }
    $this->choiceValues = $choiceValues;
    parent::__construct($fieldname, $options);
  }

  /**
   * @param string $formFieldClass
   * @param array<string, mixed> $fieldOptions
   *
   * @return FieldInterface<T>
   */
  protected function createFormField(string $formFieldClass, array $fieldOptions = array()): FieldInterface
  {
    /** @var FieldInterface<T> $field */
    $field = new $formFieldClass($this->fieldname, $this->choiceValues, $fieldOptions);
    return $field;
  }

}
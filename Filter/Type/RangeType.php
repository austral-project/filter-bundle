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

use Austral\FilterBundle\Filter\Filter;
use Austral\FilterBundle\Filter\Type\Base\FilterType;
use Austral\FilterBundle\Filter\Type\Base\FilterTypeInterface;
use Austral\FormBundle\Field as FormField;
use Austral\FormBundle\Field\Base\FieldInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Filter Range Type.
 * @template T of object
 *
 * @extends FilterType<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class RangeType extends FilterType
{

  const CONCAT_AND = "AND";
  const CONCAT_OR = "OR";

  /**
   * @var string
   */
  protected string $formFieldDefault = FormField\DatePicker::class;

  /**
   * @var array<int|string, mixed>
   */
  protected array $formFieldsAccepted = array(
    FormField\DatePicker::class,
    FormField\NumberField::class,
  );

  /**
   * @var array<string, FieldInterface<T>>
   */
  protected array $fields = array();

  /**
   * @param string $fieldname
   * @param array<string, mixed> $options
   *
   * @return RangeType<T>
   * @throws \Exception
   */
  public static function create(string $fieldname, array $options = array()): RangeType
  {
    /** @var RangeType<T> $element */
    $element = new self($fieldname, $options);
    return $element;
  }

  /**
   * RangeType constructor.
   *
   * @param string $fieldname
   * @param array<string, mixed> $options
   *
   * @throws \Exception
   */
  public function __construct(string $fieldname, array $options = array())
  {
    parent::__construct($fieldname, $options);
    if(!$this->options['value']["render"])
    {
      $this->options['value']["render"] = function($value) {
        $valuesRender = array();
        if(is_array($value))
        {
          foreach($value as $key => $oneValue)
          {
            $valuesRender[$key] = $oneValue instanceof \DateTime ? $oneValue->format("Y-m-d") : $oneValue;
          }
        }
        return $valuesRender;
      };
    }
  }

  /**
   * @param OptionsResolver $resolver
   */
  public function configureOptions(OptionsResolver $resolver): void
  {
    parent::configureOptions($resolver);
    $resolver->setDefault('range_conditions', function (OptionsResolver $subResolver) {
      $subResolver->setDefaults(array(
          "concat"    =>  self::CONCAT_AND,
          "from"      =>  FilterTypeInterface::CONDITION_SUP_EQUAL,
          "to"        =>  FilterTypeInterface::CONDITION_INF_EQUAL
        )
      );
      $subResolver->setAllowedTypes('from', array('string'));
      $subResolver->setAllowedTypes('to', array('string'));
    });
  }

  /**
   * @param string $name
   *
   * @return string
   */
  public function getRangeCondition(string $name): string
  {
    return $this->options["range_conditions"][$name];
  }

  /**
   * @param string $formFieldClass
   * @param array<string, mixed> $fieldOptions
   *
   * @return FieldInterface<T>
   */
  protected function createFormField(string $formFieldClass, array $fieldOptions = array()): FieldInterface
  {
    $fields = array();
    foreach(array("from", "to") as $filename)
    {
      $fieldOptions['setter'] = function(Filter $object, $value) use($filename){
        $object->setValueByFieldname($this->fieldname, $value, $filename);
      };
      $fieldOptions['getter'] = function(Filter $object)  use($filename){
        return $object->getValueByFieldname($this->fieldname, $filename);
      };
      /** @var FieldInterface<T> $field */
      $field = new $formFieldClass($filename, $fieldOptions);
      $fields[$filename] = $field;
    }
    return new FormField\MultiField($this->fieldname, $fields);
  }

}
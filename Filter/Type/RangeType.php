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
   * @var array|string[]
   */
  protected array $formFieldsAccepted = array(
    FormField\DatePicker::class,
    FormField\NumberField::class,
  );

  /**
   * @var array
   */
  protected array $fields;

  /**
   * @param $fieldname
   * @param array $options
   *
   * @return $this
   * @throws \Exception
   */
  public static function create($fieldname, array $options = array()): RangeType
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
  public function configureOptions(OptionsResolver $resolver)
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
   * @param $formFieldClass
   * @param $fieldOptions
   *
   * @return FieldInterface
   */
  protected function createFormField($formFieldClass, $fieldOptions): FieldInterface
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
      $fields[$filename] = new $formFieldClass($filename, $fieldOptions);
    }
    return new FormField\MultiField($this->fieldname, $fields);
  }

}
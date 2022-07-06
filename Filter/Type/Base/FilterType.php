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

use Austral\FilterBundle\Filter\Filter;
use Austral\FormBundle\Field\Base\FieldInterface;
use Austral\ToolsBundle\AustralTools;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Austral Abstract Filter Type.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @abstract
 */
abstract class FilterType implements FilterTypeInterface
{

  /**
   * @var array
   */
  protected array $formFieldsAccepted = array();

  /**
   * @var string
   */
  protected string $formFieldDefault;

  /**
   * @var string
   */
  protected string $fieldname;

  /**
   * @var FieldInterface|null
   */
  protected ?FieldInterface $formField;

  /**
   * @var FilterTypeInterface
   */
  protected FilterTypeInterface $parent;

  /**
   * @var array
   */
  protected array $children = array();

  /**
   * @var string|null|array|float|int|bool
   */
  protected $value;

  /**
   * @var array
   */
  protected array $options;

  /**
   * EntityFieldList constructor.
   *
   * @param $fieldname
   * @param array $options
   *
   * @throws \Exception
   */
  public function __construct($fieldname, array $options = array())
  {
    $this->fieldname = $fieldname;

    $resolver = new OptionsResolver();
    $this->configureOptions($resolver);
    $this->options = $resolver->resolve($options);
    $this->initFormField();
  }

  /**
   * @throws \Exception
   */
  protected function initFormField()
  {
    $formFieldClass = $this->options["formField"]["class"] ? : $this->formFieldDefault;
    if(!in_array($formFieldClass, $this->formFieldsAccepted))
    {
      throw new \Exception("{$formFieldClass} is not accepted in ".get_class($this)." !!!");
    }

    $fieldOptions = $this->options["formField"]["options"];
    $fieldOptions['setter'] = function(Filter $object, $value){
      $object->setValueByFieldname($this->fieldname, $value);
    };
    $fieldOptions['getter'] = function(Filter $object){
      return $object->getValueByFieldname($this->fieldname);
    };
    $this->formField = $this->createFormField($formFieldClass, $fieldOptions);
  }

  /**
   * @param $formFieldClass
   * @param $fieldOptions
   *
   * @return FieldInterface
   */
  protected function createFormField($formFieldClass, $fieldOptions): FieldInterface
  {
     return new $formFieldClass($this->fieldname, $fieldOptions);
  }

  /**
   * @param OptionsResolver $resolver
   */
  protected function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults(array(
        "formField"               =>  function(OptionsResolver $subResolver) {
          $subResolver->setDefault("class", null)
            ->setDefault("options", array());
          $subResolver->addAllowedTypes("class", array('string', "null"))
            ->addAllowedTypes("options", array('array'));
        },
        "query"                   =>  function(OptionsResolver $subResolver) {
          $subResolver->setDefault("condition", FilterTypeInterface::CONDITION_LIKE)
            ->setDefault("parameter_format", null)
            ->setDefault("alias", "root");

          $subResolver->addAllowedTypes("condition", array('string'))
            ->addAllowedTypes("parameter_format", array('string', "null"))
            ->addAllowedTypes("alias", array('string'));
        },
        "value"                 =>  function(OptionsResolver $subResolver) {
          $subResolver->setDefaults(array(
              "render"          =>  null,
              "transform"       =>  null
            )
          );
          $subResolver->addAllowedTypes("render", array('null', \Closure::class))
            ->addAllowedTypes("transform", array('null', \Closure::class));
        }
      )
    );
  }

  /**
   * Get fieldname
   * @return string
   */
  public function getFieldname(): string
  {
    return $this->fieldname;
  }

  /**
   * Get fieldname
   *
   * @param $fieldname
   *
   * @return FilterTypeInterface
   */
  public function setFieldname($fieldname): FilterTypeInterface
  {
    $this->fieldname = $fieldname;
    return $this;
  }

  /**
   * @return FieldInterface|null
   */
  public function getFormField(): ?FieldInterface
  {
    return $this->formField;
  }

  /**
   * Get options
   * @return array
   */
  public function getOptions(): array
  {
    return $this->options;
  }

  /**
   * @param array $options
   *
   * @return FilterTypeInterface
   */
  public function setOptions(array $options): FilterTypeInterface
  {
    $resolver = new OptionsResolver();
    $this->configureOptions($resolver);
    $this->options = $resolver->resolve($options);
    return $this;
  }

  /**
   * @param string|null $subkey
   *
   * @return string|null|array|float|int|bool
   */
  public function getValue(string $subkey = null)
  {
    return ($subkey && is_array($this->value)) ? AustralTools::getValueByKey($this->value, $subkey, null) : $this->value;
  }

  /**
   * @return string|null|array|float|int|bool
   */
  public function getRenderValue()
  {
    $value = $this->getValue();
    if($this->options['value']["render"])
    {
      $value = $this->options['value']["render"]->call($this, $value);
    }
    return $value;
  }

  /**
   * @param mixed $value
   * @param string|null $subkey
   *
   * @return FilterTypeInterface
   */
  public function setValue($value, string $subkey = null): FilterTypeInterface
  {
    if($subkey)
    {
      if(!is_array($this->value))
      {
        $this->value = array();
      }
      if($this->options['value']["transform"])
      {
        $this->value[$subkey] = $this->options['value']["transform"]->call($this, $value, $subkey);
      }
      else
      {
        $this->value[$subkey] = $value;
      }
    }
    else
    {
      if($this->options['value']["transform"])
      {
        $this->value = $this->options['value']["transform"]->call($this, $value);
      }
      else
      {
        $this->value = $value;
      }
    }

    return $this;
  }

  /**
   * @return string
   */
  public function getCondition(): string
  {
    return $this->options["query"]["condition"];
  }

  /**
   * @return string
   */
  public function getChildrenConcat(): string
  {
    return $this->options["query"]["children_concat"];
  }

  /**
   * @return string
   */
  public function getQueryAlias(): string
  {
    return $this->options["query"]["alias"];
  }

  /**
   * @param string $alias
   *
   * @return FilterTypeInterface
   */
  public function setQueryAlias(string $alias): FilterTypeInterface
  {
    $this->options["query"]["alias"] = $alias;
    return $this;
  }

  /**
   * @return string
   */
  public function getValueForQueryParameter(): string
  {
    return sprintf($this->getParameterFormat(), $this->value ? : "");
  }

  /**
   * @return ?string
   */
  protected function getParameterFormat(): ?string
  {
    if(!$queryParameterFormat = $this->options["query"]["parameter_format"])
    {
      if($this->getCondition() == FilterTypeInterface::CONDITION_LIKE)
      {
        $queryParameterFormat = "%%%s%%";
      }
      elseif($this->getCondition() == FilterTypeInterface::CONDITION_IN)
      {
        $queryParameterFormat = "(%s)";
      }
      else
      {
        $queryParameterFormat = "%s";
      }
    }
    return $queryParameterFormat;
  }

}
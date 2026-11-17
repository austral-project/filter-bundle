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
 * @template T of object
 *
 * @implements FilterTypeInterface<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @abstract
 */
abstract class FilterType implements FilterTypeInterface
{

  /**
   * @var array<int|string, mixed>
   */
  protected array $formFieldsAccepted = array();

  /**
   * @var string
   */
  protected string $formFieldDefault = "";

  /**
   * @var string
   */
  protected string $fieldname;

  /**
   * @var FieldInterface<T>|null
   */
  protected ?FieldInterface $formField;

  /**
   * @var FilterTypeInterface<T>|null
   */
  protected ?FilterTypeInterface $parent = null;

  /**
   * @var array<string, FilterTypeInterface<T>>
   */
  protected array $children = array();

  /**
   * @var mixed
   */
  protected mixed $value = null;

  /**
   * @var array<string, mixed>
   */
  protected array $options;

  /**
   * EntityFieldList constructor.
   *
   * @param string $fieldname
   * @param array<string, mixed> $options
   *
   * @throws \Exception
   */
  public function __construct(string $fieldname, array $options = array())
  {
    $this->fieldname = $fieldname;

    $resolver = new OptionsResolver();
    $this->configureOptions($resolver);
    $this->options = $resolver->resolve($options);
    $this->initFormField();
  }

  /**
   * @throws \Exception
   * @return void
   */
  protected function initFormField(): void
  {
    $formFieldClass = $this->options["formField"]["class"] ? : $this->formFieldDefault;
    if(!in_array($formFieldClass, $this->formFieldsAccepted))
    {
      throw new \Exception("{$formFieldClass} is not accepted in ".get_class($this)." !!!");
    }

    $fieldOptions = $this->options["formField"]["options"];
    if(!array_key_exists("setter", $fieldOptions))
    {
      $fieldOptions['setter'] = function(Filter $object, $value){
        $object->setValueByFieldname($this->fieldname, $value);
      };
    }
    if(!array_key_exists("getter", $fieldOptions))
    {
      $fieldOptions['getter'] = function(Filter $object){
        return $object->getValueByFieldname($this->fieldname);
      };
    }
    $this->formField = $this->createFormField($formFieldClass, $fieldOptions);
  }

  /**
   * @param string $formFieldClass
   * @param array<string, mixed> $fieldOptions
   *
   * @return FieldInterface<T>
   */
  protected function createFormField(string $formFieldClass, array $fieldOptions = array()): FieldInterface
  {
    /** @var FieldInterface<T> $formField */
     $formField = new $formFieldClass($this->fieldname, $fieldOptions);
     return $formField;
  }

  /**
   * @param OptionsResolver $resolver
   */
  protected function configureOptions(OptionsResolver $resolver): void
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
            ->setDefault("alias", "root")
            ->setDefault("fieldname", null);

          $subResolver->addAllowedTypes("condition", array('string'))
            ->addAllowedTypes("parameter_format", array('string', "null"))
            ->addAllowedTypes("alias", array('string'))
            ->addAllowedTypes("fieldname", array('string', "null"));
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
   * @inheritDoc
   */
  public function getFieldname(): string
  {
    return $this->fieldname;
  }

  /**
   * @inheritDoc
   */
  public function setFieldname(string $fieldname): self
  {
    $this->fieldname = $fieldname;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getFormField(): ?FieldInterface
  {
    return $this->formField;
  }

  /**
   * @inheritDoc
   */
  public function getOptions(): array
  {
    return $this->options;
  }

  /**
   * @inheritDoc
   */
  public function setOptions(array $options): self
  {
    $resolver = new OptionsResolver();
    $this->configureOptions($resolver);
    $this->options = $resolver->resolve($options);
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getValue(?string $subkey = null): mixed
  {
    return ($subkey && is_array($this->value)) ? AustralTools::valueByKey($this->value, $subkey) : $this->value;
  }

  /**
   * @inheritDoc
   */
  public function getRenderValue(): mixed
  {
    $value = $this->getValue();
    if($this->options['value']["render"])
    {
      $value = $this->options['value']["render"]->call($this, $value);
    }
    return $value;
  }

  /**
   * @inheritDoc
   */
  public function setValue(mixed $value, ?string $subkey = null): self
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
   * @inheritDoc
   */
  public function getCondition(): string
  {
    return $this->options["query"]["condition"];
  }

  /**
   * @inheritDoc
   */
  public function getChildrenConcat(): string
  {
    return $this->options["query"]["children_concat"];
  }

  /**
   * @inheritDoc
   */
  public function getQueryAlias(): string
  {
    return $this->options["query"]["alias"];
  }

  /**
   * @inheritDoc
   */
  public function setQueryAlias(string $alias): self
  {
    $this->options["query"]["alias"] = $alias;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getFieldnameQuery(): string
  {
    return $this->options["query"]["fieldname"] ?? $this->getFieldname();
  }

  /**
   * @inheritDoc
   */
  public function setFieldnameQuery(string $fieldname): self
  {
    $this->options["query"]["fieldname"] = $fieldname;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getValueForQueryParameter(): string
  {
    return $this->getParameterFormat() ? sprintf($this->getParameterFormat(), $this->value ? : "") : "";
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
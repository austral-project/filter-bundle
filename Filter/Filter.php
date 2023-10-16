<?php
/*
 * This file is part of the Austral Filter Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FilterBundle\Filter;

use Austral\EntityBundle\Entity\Entity;
use Austral\EntityBundle\Entity\EntityInterface;
use Austral\FilterBundle\Filter\Type\Base\FilterTypeInterface;
use Austral\EntityBundle\EntityManager\EntityManagerInterface;
use Austral\FilterBundle\Filter\Type as FilterType;
use Austral\FormBundle\Mapper\FormMapper;

use Austral\ListBundle\Filter\FilterInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Austral Filter.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class Filter extends Entity implements FilterInterface
{

  /**
   * @var FormMapper
   */
  protected FormMapper $formMapper;

  /**
   * @var EntityManagerInterface
   */
  protected EntityManagerInterface $entityManager;

  /**
   * @var FormInterface
   */
  protected FormInterface $form;

  /**
   * @var FormView|null
   */
  protected ?FormView $formView = null;

  /**
   * @var string
   */
  protected string $keyname;

  /**
   * @var array
   */
  protected array $data = array();

  /**
   * @var array
   */
  protected array $filterTypes = array();

  /**
   * @var array
   */
  protected array $leftJoin = array();

  /**
   * @param string $keyname
   * @param EntityManagerInterface $entityManager
   * @param array $data
   */
  public function __construct(string $keyname, EntityManagerInterface $entityManager, array $data = array())
  {
    parent::__construct();
    $this->formMapper = new FormMapper();
    $this->formMapper->setObject($this);
    $this->entityManager = $entityManager;
    $this->data = $data;
    $this->keyname = $keyname;
  }

  /**
   * @return string
   */
  public function getKeyname(): string
  {
    return $this->keyname;
  }

  /**
   * @return FormInterface
   */
  public function getForm(): FormInterface
  {
    return $this->form;
  }

  /**
   * @param FormInterface $form
   *
   * @return $this
   */
  public function setForm(FormInterface $form): Filter
  {
    $this->form = $form;
    return $this;
  }

  /**
   * @return FormView
   */
  public function getFormView(): FormView
  {
    if(!$this->formView)
    {
      $this->createFormView();
    }
    return $this->formView;
  }

  /**
   * @return $this
   */
  public function createFormView(): Filter
  {
    $this->formView = $this->form->createView();
    return $this;
  }

  /**
   * @return FormMapper
   */
  public function getFormMapper(): FormMapper
  {
    return $this->formMapper;
  }

  /**
   * @param FilterTypeInterface $filterType
   *
   * @return $this
   * @throws \Exception
   */
  public function add(FilterTypeInterface $filterType): Filter
  {
    if($filterType->getQueryAlias() !== "root")
    {
      if(!array_key_exists($filterType->getQueryAlias(), $this->entityManager->getFieldsMapping()))
      {
        if(!array_key_exists($filterType->getQueryAlias(), $this->entityManager->getFieldsMappingWithAssociation()))
        {
          throw new \Exception("The fieldname {$filterType->getQueryAlias()} is not defined in mapping EntityClass !!!");
        }
        else
        {
          $this->leftJoin["root.{$filterType->getQueryAlias()}"] = $filterType->getQueryAlias();
          $filterType->setQueryAlias($filterType->getQueryAlias());
        }
      }
    }
    else
    {
      if(!array_key_exists($filterType->getFieldname(), $this->entityManager->getFieldsMapping()))
      {
        if(!array_key_exists($filterType->getFieldname(), $this->entityManager->getFieldsMappingWithAssociation()))
        {
          throw new \Exception("The fieldname {$filterType->getFieldname()} is not defined in mapping EntityClass !!!");
        }
        else
        {
          $this->leftJoin["root.{$filterType->getFieldname()}"] = $filterType->getFieldname();
          $filterType->setQueryAlias($filterType->getFieldname());
        }
      }
    }

    if(array_key_exists($filterType->getFieldname(), $this->data))
    {
      $filterType->setValue($this->data[$filterType->getFieldname()]);
    }
    $this->filterTypes[$filterType->getFieldname()] = $filterType;
    $this->formMapper->add($filterType->getFormField());
    return $this;
  }

  /**
   * @param string $fieldname
   * @param string|null $subkey
   *
   * @return array|bool|float|int|string|null
   */
  public function getValueByFieldname(string $fieldname, string $subkey = null)
  {
    return $this->getFilterType($fieldname)->getValue($subkey);
  }

  /**
   * @param string $fieldname
   * @param null $value
   * @param string|null $subkey
   *
   * @return $this
   */
  public function setValueByFieldname(string $fieldname, $value = null, string $subkey = null): EntityInterface
  {
    $this->getFilterType($fieldname)->setValue($value, $subkey);
    return $this;
  }

  /**
   * @return array
   */
  public function getFilterTypes(): array
  {
    return $this->filterTypes;
  }

  /**
   * @param string $fieldname
   *
   * @return FilterTypeInterface
   */
  public function getFilterType(string $fieldname): FilterTypeInterface
  {
    return $this->filterTypes[$fieldname];
  }

  /**
   * @return boolean
   */
  public function hasFilterValue(): bool
  {
    $hasFilterValue = false;
    /**
     * @var FilterTypeInterface $filterType
     */
    foreach($this->getFilterTypes() as $filterType)
    {
      if(!is_array($filterType->getValue()) && $filterType->getValue())
      {
        $hasFilterValue = true;
      }
      elseif($filterType instanceof FilterType\RangeType)
      {
        if(is_array($filterType->getValue()))
        {
          foreach($filterType->getValue() as $value)
          {
            if($value)
            {
              $hasFilterValue = true;
            }
          }
        }
      }
    }
    return $hasFilterValue;
  }


  /**
   * @param QueryBuilder $queryBuilder
   * @param bool $addJoinSelect
   *
   * @return QueryBuilder
   */
  public function generateQueryBuilder(QueryBuilder $queryBuilder, bool $addJoinSelect = true): QueryBuilder
  {

    $aliasUsed = array();
    /**
     * @var FilterTypeInterface $filterType
     */
    foreach($this->getFilterTypes() as $filterType)
    {
      if(!is_array($filterType->getValue()) && $filterType->getValue())
      {
        if($filterType instanceof FilterType\StringType || $filterType instanceof FilterType\StringChoiceType)
        {
          $aliasUsed[] = $filterType->getQueryAlias();
          $queryBuilder->andWhere("{$filterType->getQueryAlias()}.{$filterType->getFieldnameQuery()} {$filterType->getCondition()} :{$filterType->getFieldname()}")
            ->setParameter("{$filterType->getFieldname()}", $filterType->getValueForQueryParameter());
        }
        elseif($filterType instanceof FilterType\DateType)
        {
          $queryBuilder->andWhere("{$filterType->getQueryAlias()}.{$filterType->getFieldnameQuery()} {$filterType->getCondition()} :{$filterType->getFieldname()}")
            ->setParameter("{$filterType->getFieldname()}", $filterType->getValueForQueryParameter());
        }
      }
      elseif($filterType instanceof FilterType\RangeType)
      {
        if(is_array($filterType->getValue()))
        {
          $queryForChildren = array();
          foreach($filterType->getValue() as $key => $value)
          {
            if($value)
            {
              $aliasUsed[] = $filterType->getQueryAlias();
              $queryForChildren[] = "{$filterType->getQueryAlias()}.{$filterType->getFieldname()} {$filterType->getRangeCondition($key)} :{$key}";
              $queryBuilder->setParameter($key, $value);
            }
          }
          if($queryForChildren)
          {
            $queryBuilder->andWhere(implode(" {$filterType->getRangeCondition("concat")} ", $queryForChildren));
          }
        }
      }
    }

    if($leftJoin = $this->leftJoin)
    {
      if($dqlPartsJoinAll = $queryBuilder->getDQLPart("join"))
      {
        foreach ($dqlPartsJoinAll as $dqlPartsJoin)
        {
          /** @var Join $dqlPartJoin */
          foreach($dqlPartsJoin as $dqlPartJoin)
          {
            if(array_key_exists($dqlPartJoin->getJoin(), $leftJoin))
            {
              unset($leftJoin[$dqlPartJoin->getJoin()]);
            }
          }
        }
      }
      foreach($leftJoin as $join => $alias)
      {
        if(in_array($alias, $aliasUsed)) {
          $queryBuilder->leftJoin($join, $alias);
        }
      }
    }
    return $queryBuilder;
  }

}
<?php
/*
 * This file is part of the Austral Filter Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FilterBundle\Mapper;

use Austral\EntityBundle\EntityManager\EntityManagerInterface;
use Austral\FilterBundle\Filter\Filter;
use Austral\FilterBundle\Filter\Type\Base\FilterTypeInterface;
use Austral\FilterBundle\Form\Type\FilterFormType;
use Austral\FilterBundle\Event\FilterEvent;
use Austral\ListBundle\Filter\FilterMapperInterface;
use Austral\FormBundle\Event\FormEvent;
use Austral\ToolsBundle\AustralTools;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Austral Filter Mapper.
 * @template T of object
 *
 * @implements FilterMapperInterface<T>
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class FilterMapper implements FilterMapperInterface
{

  /**
   * @var SessionInterface|null
   */
  protected ?SessionInterface $session = null;

  /**
   * @var Request|null
   */
  protected ?Request $request = null;

  /**
   * @var string|null
   */
  protected ?string $keyname = null;

  /**
   * @var EntityManagerInterface<T>|null
   */
  protected ?EntityManagerInterface $entityManager = null;

  /**
   * @var array<string, mixed>
   */
  protected array $sessionData = array();

  /**
   * @var array<string, mixed>
   */
  protected array $datas = array();

  /**
   * @var array<string, Filter<T>>
   */
  protected array $filters = array();

  /**
   * @var string
   */
  protected string $sessionName = "austral_filters";

  /**
   * @var string
   */
  protected string $translateDomain = "austral";

  /**
   * @var string
   */
  protected string $pathToTemplateDefault = "@AustralAdmin/Form/Components/Fields";

  /**
   * @param EventDispatcher $dispatcher
   * @param FilterFormType<T> $filterFormType
   * @param FormFactoryInterface $formFactory
   */
  public function __construct(
    #[Autowire(service: "event_dispatcher")] protected EventDispatcherInterface $dispatcher,
    #[Autowire(service: "austral.filter.form.type")] protected FilterFormType $filterFormType,
    #[Autowire(service: "form.factory")] protected FormFactoryInterface $formFactory)
  {
    $this->filterFormType = $filterFormType->setClass(get_class($this));
  }

  /**
   * setRequest
   *
   * @param RequestStack $requestStack
   * @return $this
   */
  public function setRequest(RequestStack $requestStack): self
  {
    $this->request = $requestStack->getCurrentRequest();
    $this->session = $requestStack->getSession();
    return $this;
  }

  /**
   * @return string|null
   * @throws \Exception
   */
  public function getKeyname(): ?string
  {
    if(!$this->keyname)
    {
      throw new \Exception("Keyname is not defined !!!");
    }
    return $this->keyname;
  }

  /**
   * @return $this
   */
  public function setKeyname(string $keyname): FilterMapper
  {
    $this->keyname = $keyname;
    return $this;
  }

  /**
   * @param SessionInterface $session
   *
   * @return $this
   */
  public function setSession(SessionInterface $session): FilterMapper
  {
    $this->session = $session;
    return $this;
  }

  /**
   * @return EntityManagerInterface<T>|null
   * @throws \Exception
   */
  public function getEntityManager(): ?EntityManagerInterface
  {
    if(!$this->entityManager)
    {
      throw new \Exception("EntityManager is not defined !!!");
    }
    return $this->entityManager;
  }

  /**
   * @param EntityManagerInterface<T> $entityManager
   *
   * @return $this
   */
  public function setEntityManager(EntityManagerInterface $entityManager): FilterMapper
  {
    $this->entityManager = $entityManager;
    return $this;
  }

  /**
   * @return array<string, mixed>
   * @throws \Exception
   */
  public function getData(): array
  {
    if(!$this->datas && $this->session && $this->getKeyname())
    {
      $sessionData = $this->session->get($this->sessionName, array());
      $this->datas = AustralTools::valueByKey($sessionData, $this->getKeyname(), array());
    }
    return $this->datas;
  }

  /**
   * @param string $filterKeyname
   *
   * @return bool
   */
  public function filterExist(string $filterKeyname): bool
  {
    return array_key_exists($filterKeyname, $this->filters());
  }

  /**
   * @param string $filterKeyname
   *
   * @return Filter<T>
   * @throws \Exception
   */
  public function filter(string $filterKeyname): Filter
  {
    if(!$this->filterExist($filterKeyname) && $this->getEntityManager())
    {
      /** @var Filter<T> $filter */
      $filter = new Filter(
        $filterKeyname,
        $this->getEntityManager(),
        AustralTools::valueByKey($this->getData(), $filterKeyname, array())
      );
      $filter->getFormMapper()
        ->setName("{$this->sessionName}_{$filterKeyname}")
        ->setTranslateDomain("austral")
        ->setPathToTemplateDefault("@AustralAdmin/Form/Components/Fields");

      $this->filterFormType->setFormMapper($filter->getFormMapper());
      $this->filterFormType->addFormMappers($filter->getFormMapper()->getKeyname(), $filter->getFormMapper());

      $this->filters[$filterKeyname] = $filter;
    }
    return $this->filters[$filterKeyname];
  }

  /**
   * @return array<string, Filter<T>>
   */
  public function filters(): array
  {
    return $this->filters;
  }

  /**
   * @param string $filterName
   * @param string $fieldname
   *
   * @return $this
   * @throws \Exception
   */
  public function cleanFilters(string $filterName = "all", string $fieldname = "all"): self
  {
    if($filterName === "all")
    {
      $filtersToClean = $this->filters();
    }
    else
    {
      $filtersToClean = array($this->filter($filterName));
    }

    /** @var Filter<T> $filterToClean */
    foreach($filtersToClean as $filterToClean)
    {
      if($fieldname === "all")
      {
        foreach($filterToClean->getFilterTypes() as $fieldname => $element)
        {
          $filterToClean->getFilterType($fieldname)->setValue(null);
        }
      }
      else
      {
        $subKey = null;
        if(strpos($fieldname, ":"))
        {
          list($fieldname, $subKey) = explode(":", $fieldname);
        }
        if(!array_key_exists($fieldname, $filterToClean->getFilterTypes()))
        {
          throw new \Exception("{$fieldname} is not defined in Filter");
        }
        $filterToClean->getFilterType($fieldname)->setValue(null, $subKey);
      }
    }
    $this->save();
    return $this;
  }

  /**
   * @throws \Exception
   * @return void
   */
  public function execute(): void
  {
    /** @var Filter<T> $filter */
    foreach($this->filters() as $filter)
    {
      $form = $this->formFactory->createNamed($filter->getFormMapper()->getKeyname(), get_class($this->filterFormType), $filter);
      $filter->setForm($form);

      $formEvent = new FormEvent($filter->getFormMapper());
      $this->dispatcher->dispatch($formEvent, FormEvent::EVENT_AUSTRAL_FORM_ADD_AUTO_FIELDS_BEFORE);
      $this->dispatcher->dispatch($formEvent, FormEvent::EVENT_AUSTRAL_FORM_ADD_AUTO_FIELDS_AFTER);
      $formEvent->setForm($form);
      $this->dispatcher->dispatch($formEvent, FormEvent::EVENT_AUSTRAL_FORM_INIT_END);
      if($this->request?->getMethod() === 'POST') {
        $form->handleRequest($this->request);
      }
      $filter->createFormView();

      $filterEvent = new FilterEvent($filter);
      $this->dispatcher->dispatch($filterEvent, FilterEvent::EVENT_AUSTRAL_FILTER_END);

    }
    $this->save();
  }

  /**
   * @return $this
   * @throws \Exception
   */
  public function save(): self
  {
    $sessionData = $this->session?->get($this->sessionName, array());
    /**
     * @var string $keyname
     * @var Filter<T> $filter
     */
    foreach($this->filters() as $keyname => $filter)
    {
      $datas = array();
      /** @var FilterTypeInterface<T> $element */
      foreach($filter->getFilterTypes() as $element)
      {
        $datas[$element->getFieldname()] = $element->getValue();
      }
      $this->datas[$keyname] = $datas;
    }
    $sessionData[$this->keyname] = $this->datas;
    $this->session?->set($this->sessionName, $sessionData);
    return $this;
  }

}
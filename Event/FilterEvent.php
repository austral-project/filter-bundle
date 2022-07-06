<?php
/*
 * This file is part of the Austral Filter Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FilterBundle\Event;

use Austral\FilterBundle\Filter\Filter;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Austral Form Event.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class FilterEvent extends Event
{
  const EVENT_AUSTRAL_FILTER_END = "austral.event.filter_mapper.end";

  /**
   * @var Filter
   */
  protected Filter $filter;

  /**
   * FormEvent constructor.
   *
   * @param Filter $filter
   */
  public function __construct(Filter $filter)
  {
    $this->filter = $filter;
  }

  /**
   * @return Filter
   */
  public function getFilter(): Filter
  {
    return $this->filter;
  }

  /**
   * @param Filter $filter
   *
   * @return $this
   */
  public function setFilter(Filter $filter): FilterEvent
  {
    $this->filter = $filter;
    return $this;
  }

}
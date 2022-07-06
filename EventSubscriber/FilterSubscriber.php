<?php
/*
 * This file is part of the Austral Filter Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Austral\FilterBundle\EventSubscriber;

use Austral\FilterBundle\Event\FilterEvent;
use Austral\ListBundle\Event\ListEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Austral FilterSubscriber.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class FilterSubscriber implements EventSubscriberInterface
{
  /**
   * @return array
   */
  public static function getSubscribedEvents(): array
  {
    return [
      FilterEvent::EVENT_AUSTRAL_FILTER_END =>  ["end", 0],
    ];
  }

  /**
   * @var EventDispatcherInterface
   */
  protected EventDispatcherInterface $dispatcher;

  public function __construct(EventDispatcherInterface $dispatcher)
  {
    $this->dispatcher = $dispatcher;
  }

  /**
   * @param FilterEvent $filterEvent
   */
  public function end(FilterEvent $filterEvent)
  {
    if(class_exists(ListEvent::class))
    {
      $this->dispatcher->addListener(ListEvent::EVENT_AUSTRAL_LIST_FILTER, function(ListEvent $listEvent) use($filterEvent){
        if($filterEvent->getFilter()->getKeyname() == $listEvent->getKeyname())
        {
          $listEvent->getDataHydrate()->eventFilter($filterEvent->getFilter());
        }
      });
    }

  }

}
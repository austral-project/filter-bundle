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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Austral FilterSubscriber.
 * @template T of object
 *
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class FilterSubscriber implements EventSubscriberInterface
{
  /**
   * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
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
   * @param FilterEvent<T> $filterEvent
   */
  public function end(FilterEvent $filterEvent): void
  {
    //if(class_exists(Austral\ListBundle\Event\ListEvent::class))
    //{
    //  $this->dispatcher->addListener(Austral\ListBundle\Event\ListEvent::EVENT_AUSTRAL_LIST_FILTER, function(Austral\ListBundle\Event\ListEvent $listEvent) use($filterEvent){
    //    if($filterEvent->getFilter()->getKeyname() == $listEvent->getKeyname())
    //    {
    //      $listEvent->getDataHydrate()->eventFilter($filterEvent->getFilter());
    //    }
    //  });
    //}
  }

}
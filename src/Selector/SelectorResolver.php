<?php

declare(strict_types=1);

namespace PhpAT\Selector;

use PhpAT\App\Event\WarningEvent;
use PHPAT\EventDispatcher\EventDispatcher;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class SelectorResolver
 *
 * @package PhpAT\Selector
 */
class SelectorResolver
{
    /**
     * @var ContainerBuilder
     */
    private $container;
    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * SelectorResolver constructor.
     *
     * @param ContainerBuilder $container
     */
    public function __construct(ContainerBuilder $container, EventDispatcher $dispatcher)
    {
        $this->container = $container;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param SelectorInterface $selector
     * @param array             $astMap
     * @return array
     */
    public function resolve(SelectorInterface $selector, array $astMap): array
    {
        foreach ($selector->getDependencies() as $dependency) {
            try {
                $d[$dependency] = $this->container->get($dependency);
            } catch (\Throwable $e) {
            }
        }

        $selector->injectDependencies($d ?? []);
        $selector->setAstMap($astMap);
        $selected = $selector->select();

        if (empty($selected)) {
            $this->dispatcher->dispatch(
                new WarningEvent(
                    get_class($selector) . ' (' . $selector->getParameter() . ')' . ' could not find any class'
                )
            );
        }

        return $selected;
    }
}

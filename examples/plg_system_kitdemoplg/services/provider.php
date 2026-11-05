<?php

/**
 * @copyright   Copyright (C) 2026 Cybersalt. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Acme\Plugin\System\Kitdemoplg\Extension\Kitdemoplg;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                return new Kitdemoplg(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('system', 'kitdemoplg')
                );
            }
        );
    }
};

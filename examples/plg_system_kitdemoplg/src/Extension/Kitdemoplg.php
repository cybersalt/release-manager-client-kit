<?php

/**
 * @copyright   Copyright (C) 2026 Cybersalt. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

namespace Acme\Plugin\System\Kitdemoplg\Extension;

defined('_JEXEC') or die;

use Acme\Plugin\System\Kitdemoplg\Helper\AccountLinkHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

final class Kitdemoplg extends CMSPlugin implements SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return ['onAjaxKitdemoplg' => 'onAjaxKitdemoplg'];
    }

    /** The one method the kit adds. The panel posts here through com_ajax. */
    public function onAjaxKitdemoplg(Event $event): void
    {
        $result   = $event->getArgument('result', []);
        $result[] = AccountLinkHelper::handleRequest();

        $event->setArgument('result', $result);
    }
}

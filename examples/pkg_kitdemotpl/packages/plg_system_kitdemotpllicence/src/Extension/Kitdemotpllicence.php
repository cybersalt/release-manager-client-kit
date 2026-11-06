<?php

/**
 * @copyright   Copyright (C) 2026 Cybersalt. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

namespace Acme\Plugin\System\Kitdemotpllicence\Extension;

defined('_JEXEC') or die;

use Acme\Plugin\System\Kitdemotpllicence\Helper\AccountLinkHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

final class Kitdemotpllicence extends CMSPlugin implements SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return ['onAjaxKitdemotpllicence' => 'onAjaxKitdemotpllicence'];
    }

    /** The one method the kit adds. The panel posts here through com_ajax. */
    public function onAjaxKitdemotpllicence(Event $event): void
    {
        $result   = $event->getArgument('result', []);
        $result[] = AccountLinkHelper::handleRequest();

        $event->setArgument('result', $result);
    }
}

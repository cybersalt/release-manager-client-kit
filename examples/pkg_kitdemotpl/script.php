<?php

/**
 * @copyright   Copyright (C) 2026 Cybersalt. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

class Pkg_kitdemotplInstallerScript
{
    public function postflight(string $type, $adapter): bool
    {
        $class = 'Acme\\Plugin\\System\\Kitdemotpllicence\\Helper\\AccountLinkHelper';
        $file  = JPATH_PLUGINS . '/system/kitdemotpllicence' . '/src/Helper/AccountLinkHelper.php';

        try {
            if (!class_exists($class, false) && is_file($file)) {
                require_once $file;
            }

            if (class_exists($class, false)) {
                if ($type === 'install') {
                    $class::noticeOnInstall();
                }

                register_shutdown_function(static function () use ($class): void {
                    try {
                        $class::syncUpdateSite();
                    } catch (\Throwable $e) {
                        // The panel puts it back when it is next opened.
                    }
                });
            }
        } catch (\Throwable $e) {
            // Not worth failing an install over; the panel puts it back when opened.
        }

        // Joomla installs plugins disabled, and the panel needs this one on.
        if ($type === 'install') {
            $db = \Joomla\CMS\Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);

            $db->setQuery(
                $db->getQuery(true)
                    ->update($db->quoteName('#__extensions'))
                    ->set($db->quoteName('enabled') . ' = 1')
                    ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                    ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
                    ->where($db->quoteName('element') . ' = ' . $db->quote('kitdemotpllicence'))
            )->execute();
        }

        return true;
    }
}

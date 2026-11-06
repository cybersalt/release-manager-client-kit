<?php

/**
 * @copyright   Copyright (C) 2026 Cybersalt. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

class PlgSystemKitdemoplgInstallerScript
{
    public function postflight(string $type, $adapter): bool
    {
        $class = 'Acme\\Plugin\\System\\Kitdemoplg\\Helper\\AccountLinkHelper';
        $file  = JPATH_PLUGINS . '/system/kitdemoplg/src/Helper/AccountLinkHelper.php';

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

        return true;
    }
}

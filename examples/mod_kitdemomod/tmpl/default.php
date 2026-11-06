<?php

/**
 * @copyright   Copyright (C) 2026 Cybersalt. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/** @var \Joomla\Registry\Registry $params */
?>
<p><?php echo htmlspecialchars((string) $params->get('greeting', 'Hello from the demo module'), ENT_QUOTES, 'UTF-8'); ?></p>

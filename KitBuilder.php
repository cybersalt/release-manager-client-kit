<?php

/**
 * @package     CsReleaseManager
 * @copyright   Copyright (C) 2026 Cybersalt. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

namespace Cybersalt\Component\CsReleaseManager\Administrator\Helper;

defined('_JEXEC') || defined('CSRM_KIT_CLI') or die;

/**
 * Fills the client kit's templates with one extension's values.
 *
 * It has no Joomla dependency, because the public kit repository runs this same
 * file from the command line (generate.php). Everything the caller supplies ends
 * up inside generated PHP, XML or INI, so validate() is strict about each value's
 * shape, and text that can't be restricted is escaped per file type in tokens().
 */
final class KitBuilder
{
    /**
     * Why these options can't build a kit. Empty when they can.
     *
     * Options: store_base, package_element, package_key, ext_type (component or
     * plugin), ext_element (com_x, or the plugin's element), ext_folder (plugin
     * group), namespace (your manifest's <namespace>), product, store_name, support.
     *
     * @return string[]
     */
    public static function validate(array $o): array
    {
        $errors = [];

        if (!preg_match('#^https?://[a-zA-Z0-9.-]+(:\d{1,5})?(/[a-zA-Z0-9._~/-]*)?$#D', (string) ($o['store_base'] ?? ''))) {
            $errors[] = 'The store URL must be a plain http or https address.';
        }

        if (!preg_match('/^[a-zA-Z0-9_.-]{1,100}$/D', (string) ($o['package_element'] ?? ''))) {
            $errors[] = 'The package element can only contain letters, digits, dots, dashes and underscores.';
        }

        if (!preg_match('/^[a-z0-9._-]{0,100}$/D', (string) ($o['package_key'] ?? ''))) {
            $errors[] = 'The feed key can only contain lowercase letters, digits, dots, dashes and underscores.';
        }

        $type    = (string) ($o['ext_type'] ?? '');
        $element = (string) ($o['ext_element'] ?? '');

        if ($type === 'component') {
            if (!preg_match('/^com_[a-z0-9_]{1,90}$/D', $element)) {
                $errors[] = 'A component element looks like com_myextension, in lowercase.';
            }
        } elseif ($type === 'plugin') {
            if (!preg_match('/^[a-z0-9_]{1,90}$/D', $element)) {
                $errors[] = 'A plugin element looks like myextension, in lowercase, with no plg_ prefix.';
            }

            if (!preg_match('/^[a-z0-9_]{1,50}$/D', (string) ($o['ext_folder'] ?? ''))) {
                $errors[] = 'A plugin needs its group, such as system or content, in lowercase.';
            }
        } else {
            $errors[] = 'The kit supports components and plugins.';
        }

        if (!preg_match('/^[A-Z][A-Za-z0-9_]*(\\\\[A-Z][A-Za-z0-9_]*)+$/D', (string) ($o['namespace'] ?? ''))) {
            $errors[] = 'The namespace is the value in your manifest\'s <namespace> tag, for example Acme\\Component\\Gallery.';
        }

        if (trim((string) ($o['product'] ?? '')) === '') {
            $errors[] = 'The product name is required.';
        }

        return $errors;
    }

    /**
     * Every file in the kit, as output path => contents.
     *
     * @return array<string, string>
     */
    public static function files(array $o, string $templates): array
    {
        $map = $o['ext_type'] === 'plugin' ? self::pluginMap($o) : self::componentMap($o);
        $map = ['common/test-link.sh.tpl' => 'test-link.sh'] + $map;

        $tokens = self::tokens($o);
        $files  = [];

        foreach ($map as $template => $output) {
            $contents = file_get_contents(rtrim($templates, '/\\') . '/' . $template);

            if ($contents === false) {
                throw new \RuntimeException('Missing kit template: ' . $template);
            }

            $files[$output] = strtr($contents, $tokens);
        }

        return $files;
    }

    /** The update feed URL for the manifest, not yet XML-encoded. */
    public static function feedUrl(array $o): string
    {
        $url = rtrim((string) $o['store_base'], '/') . '/index.php?option=com_csreleasemanager&task=api.updatexml&format=raw&element=' . $o['package_element'];

        if ((string) ($o['package_key'] ?? '') !== '') {
            $url .= '&package=' . $o['package_key'];
        }

        return $url;
    }

    private static function componentMap(array $o): array
    {
        $lang = 'admin/language/en-GB/' . $o['ext_element'] . '.account.ini';

        return [
            'component/README.md.tpl'          => 'README.md',
            'component/manifest.xml.tpl'       => 'snippets/manifest.xml',
            'component/postflight.php.tpl'     => 'snippets/postflight.php',
            'common/AccountLinkHelper.php.tpl' => 'admin/src/Helper/AccountLinkHelper.php',
            'common/account-panel.php.tpl'     => 'admin/src/Helper/account-panel.php',
            'common/account.ini.tpl'           => $lang,
            'component/AccountController.php.tpl' => 'admin/src/Controller/AccountController.php',
            'component/HtmlView.php.tpl'       => 'admin/src/View/Account/HtmlView.php',
            'component/default.php.tpl'        => 'admin/tmpl/account/default.php',
        ];
    }

    private static function pluginMap(array $o): array
    {
        $lang = 'language/en-GB/plg_' . $o['ext_folder'] . '_' . $o['ext_element'] . '.account.ini';

        return [
            'plugin/README.md.tpl'             => 'README.md',
            'plugin/manifest.xml.tpl'          => 'snippets/manifest.xml',
            'plugin/plugin-method.php.tpl'     => 'snippets/plugin-method.php',
            'component/postflight.php.tpl'     => 'snippets/postflight.php',
            'common/AccountLinkHelper.php.tpl' => 'src/Helper/AccountLinkHelper.php',
            'common/account-panel.php.tpl'     => 'src/Helper/account-panel.php',
            'common/account.ini.tpl'           => $lang,
            'plugin/AccountlinkField.php.tpl'  => 'src/Field/AccountlinkField.php',
        ];
    }

    private static function tokens(array $o): array
    {
        $isPlugin = $o['ext_type'] === 'plugin';
        $element  = (string) $o['ext_element'];
        $folder   = $isPlugin ? (string) $o['ext_folder'] : '';
        $key      = $isPlugin ? 'plg_' . $folder . '_' . $element : $element;
        $ns       = (string) $o['namespace'];
        $helperNs = $ns . ($isPlugin ? '\\Helper' : '\\Administrator\\Helper');
        $product  = trim((string) $o['product']);
        $store    = trim((string) ($o['store_name'] ?? '')) ?: $product;
        $support  = trim((string) ($o['support'] ?? '')) ?: 'support';

        // The panel prints these language strings as HTML, so they are escaped here.
        $ini = static fn (string $text): string => str_replace(["\n", "\r"], ' ', htmlspecialchars($text, ENT_COMPAT, 'UTF-8', false));
        $xml = static fn (string $text): string => htmlspecialchars($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $md  = static fn (string $text): string => str_replace(["\n", "\r"], ' ', $text);

        return [
            '{{STORE_BASE}}'      => rtrim((string) $o['store_base'], '/'),
            '{{PACKAGE_ELEMENT}}' => (string) $o['package_element'],
            '{{PACKAGE_KEY}}'     => (string) ($o['package_key'] ?? ''),
            '{{EXT_TYPE}}'        => (string) $o['ext_type'],
            '{{EXT_ELEMENT}}'     => $element,
            '{{EXT_FOLDER}}'      => $folder,
            '{{EXT_KEY}}'         => $key,
            '{{NAMESPACE}}'       => $ns,
            '{{HELPER_NS}}'       => $helperNs,
            '{{LANG_EXTENSION}}'  => $key . '.account',
            '{{LANG_PREFIX}}'     => strtoupper($key),
            '{{AJAX_URL}}'        => $isPlugin
                ? 'index.php?option=com_ajax&plugin=' . $element . '&group=' . $folder . '&format=raw'
                : 'index.php?option=' . $element . '&task=account.run&format=raw',
            '{{AJAX_METHOD}}'     => 'onAjax' . ucfirst($element),
            '{{HELPER_FILE}}'     => $isPlugin
                ? "JPATH_PLUGINS . '/" . $folder . '/' . $element . "/src/Helper/AccountLinkHelper.php'"
                : "JPATH_ADMINISTRATOR . '/components/" . $element . "/src/Helper/AccountLinkHelper.php'",
            '{{SCRIPT_CLASS}}'    => ucfirst(str_replace(['-', '.'], '_', (string) $o['package_element'])) . 'InstallerScript',
            '{{FEED_URL_XML}}'    => $xml(self::feedUrl($o)),
            '{{PRODUCT}}'         => $md($product),
            '{{PRODUCT_INI}}'     => $ini($product),
            '{{PRODUCT_XML}}'     => $xml($product),
            '{{STORE_NAME}}'      => $md($store),
            '{{STORE_NAME_INI}}'  => $ini($store),
            '{{SUPPORT_INI}}'     => $ini($support),
        ];
    }
}

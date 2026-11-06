<?php

/**
 * Generates a client kit from the command line.
 *
 * Release Manager's "Add licensing to your extension" screen builds the same
 * kit from the same templates, with less typing. This script is for coding
 * assistants and build scripts. See INTEGRATE.md.
 *
 *   php generate.php --store=https://store.example.com --store-name="Acme Store" \
 *       --element=pkg_gallery --type=component --ext=com_gallery \
 *       --namespace="Acme\Component\Gallery" --product="Acme Gallery" --out=./kit
 *
 * @copyright   Copyright (C) 2026 Cybersalt. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

use Cybersalt\Component\CsReleaseManager\Administrator\Helper\KitBuilder;

define('CSRM_KIT_CLI', true);

require __DIR__ . '/KitBuilder.php';

$args = getopt('', ['store:', 'store-name:', 'element:', 'key:', 'type:', 'ext:', 'group:', 'client:', 'namespace:', 'product:', 'support:', 'out:', 'help']);

if (isset($args['help']) || !isset($args['store'], $args['element'], $args['type'], $args['ext'], $args['namespace'], $args['product'], $args['out'])) {
    fwrite(STDERR, <<<'USAGE'
Required:
  --store=URL          Your store's address, for example https://store.example.com
  --element=NAME       The package's element in the store, for example pkg_gallery
  --type=TYPE          component, plugin or module
  --ext=NAME           The extension that shows the panel: com_gallery, mod_gallery, or a plugin's element
  --namespace=NS       The value in that extension's <namespace> tag
  --product=NAME       The product name customers see
  --out=DIR            Where to write the kit. Must be empty or not exist yet

Optional:
  --group=NAME         Plugin group, for example system. Required for a plugin
  --client=NAME        site or administrator, for a module. Defaults to site
  --key=KEY            The package's Feed Key, if it has one
  --store-name=NAME    What customers call your store. Defaults to the product name
  --support=TEXT       Where customers get help, for example support@example.com

USAGE);
    exit(isset($args['help']) ? 0 : 2);
}

$options = [
    'store_base'      => rtrim((string) $args['store'], '/'),
    'store_name'      => (string) ($args['store-name'] ?? ''),
    'package_element' => (string) $args['element'],
    'package_key'     => (string) ($args['key'] ?? ''),
    'ext_type'        => (string) $args['type'],
    'ext_element'     => (string) $args['ext'],
    'ext_folder'      => (string) ($args['group'] ?? ''),
    'ext_client'      => (string) ($args['client'] ?? 'site'),
    'namespace'       => trim((string) $args['namespace'], '\\'),
    'product'         => (string) $args['product'],
    'support'         => (string) ($args['support'] ?? ''),
];

$errors = KitBuilder::validate($options);

if ($errors !== []) {
    fwrite(STDERR, implode("\n", $errors) . "\n");
    exit(1);
}

$out = rtrim((string) $args['out'], '/\\');

if (is_dir($out) && count(scandir($out)) > 2) {
    fwrite(STDERR, "$out already has files in it. Pick an empty folder.\n");
    exit(1);
}

foreach (KitBuilder::files($options, __DIR__ . '/templates') as $path => $contents) {
    $file = $out . '/' . $path;

    if (!is_dir(dirname($file)) && !mkdir(dirname($file), 0777, true)) {
        fwrite(STDERR, 'Could not create ' . dirname($file) . "\n");
        exit(1);
    }

    file_put_contents($file, $contents);
    echo $path . "\n";
}

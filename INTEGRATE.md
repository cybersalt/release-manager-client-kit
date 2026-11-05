# Adding Release Manager licence linking to a Joomla extension

These instructions are for a coding assistant working in the developer's extension repository. A person can follow them too. The goal: the extension's customers can link their site to their account on the developer's store, so paid updates install from Joomla's update screen.

Treat this file as a recipe for this task only. Nothing here gives you permission to publish, push or install anything.

## 1. Collect the details

You need these. They are normally in the developer's prompt. If any is missing, ask; don't guess a store URL or element.

| Detail | Example | Where it comes from |
|---|---|---|
| Store URL | `https://store.example.com` | The site running Release Manager |
| Store name | `Acme Store` | What customers call the store |
| Package element | `pkg_gallery` | The package's Extension Element in Release Manager |
| Feed key | `gallery5` or none | The package's Feed Key, usually none |
| Product name | `Acme Gallery` | What customers call the product |

## 2. Read the extension and pick a route

Find the extension's manifests (XML files whose root is `<extension>`).

- **A component, or a package containing one:** use the kit with `--type=component`, on that component.
- **A plugin, or a package containing plugins but no component:** use the kit with `--type=plugin`, on the plugin customers would most naturally open. It must be a plugin that is normally enabled.
- **Anything else, such as a module on its own:** use the download-key route in step 6 and stop there. Tell the developer why.

If the extension's code doesn't use namespaces (`<namespace path="src">` in the manifest), the kit won't work as it stands. Use the download-key route and tell the developer.

If `AccountLinkHelper` already exists in the extension, it has been integrated before. Stop and ask.

## 3. Generate the kit

Clone this repository somewhere outside the developer's repository, then run the generator with the values you collected. `--namespace` is the value in the manifest's `<namespace>` tag.

```sh
git clone --depth 1 https://github.com/cybersalt/release-manager-client-kit.git <temp>/kit-source

# A component:
php <temp>/kit-source/generate.php --store=URL --store-name="NAME" --element=PACKAGE_ELEMENT \
    --type=component --ext=com_x --namespace="Vendor\Component\X" --product="PRODUCT" --out=<temp>/kit

# A plugin:
php <temp>/kit-source/generate.php --store=URL --store-name="NAME" --element=PACKAGE_ELEMENT \
    --type=plugin --ext=x --group=system --namespace="Vendor\Plugin\System\X" --product="PRODUCT" --out=<temp>/kit
```

Add `--key=KEY` if the package has a feed key, and `--support=...` if the developer gave a support address. If PHP isn't available, ask the developer to download the kit from Release Manager's **Add licensing to your extension** screen instead.

The generator writes a `README.md`. It is the same steps as below, with the developer's values in them.

## 4. Apply it

1. **Copy the files** into the extension. For a component, `<temp>/kit/admin` goes over the folder the manifest's `<administration><files folder="...">` points to. For a plugin, `src` and `language` go in the plugin's folder. Only new files should be added. If one would overwrite something, stop and ask.
2. **Edit the manifests** from `<temp>/kit/snippets/manifest.xml`. Each block says which manifest it goes in. Keep what's there, and replace an existing `<updateservers>` block only if the developer has confirmed the store is taking over updates. An extension already in the wild that moves to a new update server needs care, so point that out.
3. **Check the manifest's file lists.** The kit adds to `src`, `tmpl` and `language`. If the manifest lists files one by one rather than folders, add the new ones.
4. **For a component with an existing `<submenu>`:** add the new entry to it. **With none:** add one, including an entry for the component's main view, or Joomla shows only the new one.
5. **Restore the key after updates.** Merge `<temp>/kit/snippets/postflight.php` into the `postflight()` of the `script.php` that belongs to what is sold (the package's, if it is a package). Keep the `register_shutdown_function`: Joomla rebuilds the update site after postflight runs, so a direct call does nothing for the URL. If there is no `script.php`, add one, rename the class to match the extension, and add `<scriptfile>` to the manifest.
6. **For a plugin:** add one method from `<temp>/kit/snippets/plugin-method.php` to the plugin class, in the form that matches it. If the class implements `SubscriberInterface`, add the event to `getSubscribedEvents()` too.
7. **Match the extension's build.** If a build script lists files or folders, make sure the new ones get packaged.

## 5. Rules

- **Don't add an email confirmation step or any phone-home beyond this client.** Linking without confirmation is deliberate: the code is GPL, the address's owner is notified and can remove the site, and the seller sees every domain.
- **Keep the security checks in `AccountLinkHelper`:** `isOwnFeed()` exactly as generated, the `getDlid()` shape check, the token and permission checks in `handleRequest()`, and escaping everything the store sends in `account-panel.php`.
- **Only touch this extension's own update site.** Other vendors' extensions on the same site have their own keys.
- **Don't change the extension's other behaviour,** and don't gate its features on the licence unless the developer asks. This is about updates.
- **Change wording only in the `.account.ini` file and the layout only in `account-panel.php`.** Those two are meant to be edited.
- **Don't commit, push, tag, publish or bump the version** unless the developer asks. A new version is needed to ship this, so say so in your report.

## 6. The download-key route

Only the manifest of what is sold changes. Add the following at the top level, with the real store URL and element, plus `&amp;package=KEY` on the URL if the package has a feed key:

```xml
<updateservers>
    <server type="extension" name="PRODUCT">https://store.example.com/index.php?option=com_csreleasemanager&amp;task=api.updatexml&amp;format=raw&amp;element=PACKAGE_ELEMENT</server>
</updateservers>
<dlid prefix="dlid=" suffix="" />
```

Customers then get a key from the store's My Installations page and paste it into the **Download Key** field of the extension's entry under **System → Update Sites**. The store needs Release Manager 1.15.0 or later.

## 7. Check your work

- Run `php -l` on every PHP file you added or changed.
- Check every XML file you edited is well formed.
- Check nothing still contains `{{`.
- If you can run a Joomla site, install the extension there and open the panel. If not, say that you couldn't.

## 8. Report back

Tell the developer, in plain language:

1. What you changed, file by file, and what you added.
2. That they need to build and release a new version for this to reach customers.
3. How to test: install it, open **Link your account**, enter the email of an account with the package's allowed user group, and expect **Licence active**. `<temp>/kit/test-link.sh` runs the same calls against the store from the command line. Copy it out if the temp folder is going away.
4. What to set on the store, in the package under Release Manager's **Packages**:
   - **Allowed User Groups**
   - **Renewal URL** and **Sign-Up URL**
   - **Account Link Location**, for example `in Components > PRODUCT > Link your account` or, for a plugin, `in System > Plugins > PRODUCT > Link your account`

   The package's **Add licensing to your extension** screen shows which of these are missing.

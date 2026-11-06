# Adding Release Manager licence linking to a Joomla extension

These instructions are for a coding assistant working in the developer's extension repository. A person can follow them too. The goal: the extension's customers type the email they bought with into the extension, the site registers with the developer's store, and paid updates install from Joomla's update screen.

Activation is by email, always. There are no keys for a customer to get or paste.

Treat this file as a recipe for this task only. Nothing here gives you permission to publish, push or install anything.

## 1. Collect the details

You need these. They are normally in the developer's prompt. If any is missing, ask; don't guess a store URL or element.

| Detail | Example | Where it comes from |
|---|---|---|
| Store URL | `https://store.example.com` | The site running Release Manager |
| Store name | `Acme Store` | What customers call the store |
| Package element | `pkg_gallery` | The package's Extension Element in Release Manager. It's whatever is sold: `pkg_x`, `com_x`, `mod_x`, or a plugin's element |
| Feed key | `gallery5` or none | The package's Feed Key, usually none |
| Product name | `Acme Gallery` | What customers call the product |

## 2. Read the extension and pick a route

Find the extension's manifests (XML files whose root is `<extension>`). Use the first of these that fits:

- **A component, or a package containing one,** if its manifest has `<namespace path="src">`: use the kit with `--type=component`, on that component.
- **A plugin, or a package containing one:** use the kit with `--type=plugin`, on the plugin customers would most naturally open. It must be a plugin that is normally enabled.
- **A module, or a package containing one:** use the kit with `--type=module`, on that module. Add `--client=administrator` if the manifest has `client="administrator"`.
- **Anything else,** such as a template or a library, or a component with no namespace: add a small plugin, as section 6 explains. Tell the developer why.

If a plugin or module has no `<namespace>` tag, it can still use the kit. Add the tag from the snippet, choosing `Vendor\Plugin\Group\Name` or `Vendor\Module\Name` for the namespace: the vendor is the developer's company name, and the name is the extension's, each starting with a capital. Use the same value everywhere you need it. Its own code keeps working as it did and doesn't have to change.

If `AccountLinkHelper` already exists in the extension, it has been integrated before. Stop and ask.

## 3. Generate the kit

Clone this repository somewhere outside the developer's repository, then run the generator with the values you collected. `--namespace` is the value in the manifest's `<namespace>` tag, exactly as written there: no `\Administrator` or `\Site` on the end. Joomla adds those itself for components and modules, which is why the generated classes have them and the manifest tag doesn't. `--out` must be empty or not exist yet.

```sh
git clone --depth 1 https://github.com/cybersalt/release-manager-client-kit.git <temp>/kit-source

# A component:
php <temp>/kit-source/generate.php --store=URL --store-name="NAME" --element=PACKAGE_ELEMENT \
    --type=component --ext=com_x --namespace="Vendor\Component\X" --product="PRODUCT" --out=<temp>/kit

# A plugin:
php <temp>/kit-source/generate.php --store=URL --store-name="NAME" --element=PACKAGE_ELEMENT \
    --type=plugin --ext=x --group=system --namespace="Vendor\Plugin\System\X" --product="PRODUCT" --out=<temp>/kit

# A module:
php <temp>/kit-source/generate.php --store=URL --store-name="NAME" --element=PACKAGE_ELEMENT \
    --type=module --ext=mod_x --namespace="Vendor\Module\X" --product="PRODUCT" --out=<temp>/kit
```

Add `--key=KEY` if the package has a feed key, and `--support=...` if the developer gave a support address. If PHP isn't available, ask the developer to download the kit from Release Manager's **Add licensing to your extension** screen instead.

The generator writes a `README.md`. It is the same steps as below, with the developer's values in them.

## 4. Apply it

1. **Copy the files** into the extension. For a component, `<temp>/kit/admin` goes over the folder the manifest's `<administration><files folder="...">` points to. For a plugin or module, `src` and `language` go in the folder holding its manifest. Only new files should be added. If one would overwrite something, stop and ask.
2. **Edit the manifests** from `<temp>/kit/snippets/manifest.xml`. Each block says which manifest it goes in and where. Replace an existing `<updateservers>` block only if the developer has confirmed the store is taking over updates: every site that installs this version will then look at the store. Point that out in your report.
3. **Remove any `<dlid>` tag** from the manifest of what is sold, and say so in your report. It makes Joomla show customers a "Download Key missing" warning and a box to paste a key into.
4. **Check the manifest's file lists.** The kit adds to `src`, `language` and, for a component, `tmpl`. If the manifest lists files one by one rather than folders, add the new ones.
5. **For a component with an existing `<submenu>`:** add only the new entry to it. **With none:** add the snippet's whole block, and give its first entry the same text as the manifest's `<menu>` tag, so the main view stays in the menu. The "Link your account" label is plain text; use a language key instead if the extension's other menu entries do.
6. **Restore the link after updates.** Merge `<temp>/kit/snippets/postflight.php` into the `postflight()` of the `script.php` that belongs to what is sold (the package's, if it is a package). Keep the `register_shutdown_function`: Joomla rebuilds the update site after postflight runs, so a direct call does nothing for the URL. Keep the `noticeOnInstall()` call too: it's how a new customer learns where to enter their email. If there is no `script.php`, put the snippet next to that manifest as `script.php` and add `<scriptfile>script.php</scriptfile>` to the manifest. **Keep the generated class name:** Joomla silently skips the script if the name is wrong (a plugin sold on its own is `Plg<Group><Element>InstallerScript`, anything else is its element followed by `InstallerScript`). If the extension already has a `script.php`, merge only the body of the method.
7. **For a plugin:** add one method from `<temp>/kit/snippets/plugin-method.php` to the plugin class, in the form that matches it. If the class implements `SubscriberInterface`, add the event to `getSubscribedEvents()` too. A module needs no method; its field answers the panel.
8. **Match the extension's build.** If a build script lists files or folders, make sure the new ones get packaged.

## 5. Rules

- **Email only.** Don't add a licence key field, a `<dlid>` tag, or any instruction telling customers to paste something under System → Update Sites.
- **Don't add an email confirmation step or any phone-home beyond this client.** Linking without confirmation is deliberate: the code is GPL, the address's owner is notified and can remove the site, and the seller sees every domain.
- **Keep the security checks in `AccountLinkHelper`:** `isOwnFeed()` exactly as generated, the `getDlid()` shape check, the token and permission checks in `handleRequest()`, and escaping everything the store sends in `account-panel.php`.
- **Check the extension doesn't already use `custom_data` in `#__extensions`.** The link is kept there, under the key `csrm_account`. If the extension stores something else in that column, it needs to be JSON for the two to live side by side, so stop and ask.
- **Only touch this extension's own update site.** Other vendors' extensions on the same site have their own.
- **Don't change the extension's other behaviour,** and don't gate its features on the licence unless the developer asks. This is about updates.
- **Change wording only in the `.account.ini` file and the layout only in `account-panel.php`.** Those two are meant to be edited.
- **Don't commit, push, tag, publish or bump the version** unless the developer asks. A new version is needed to ship this, so say so in your report.

## 6. Anything else: add a small plugin

A template, a library, or a component with no namespace has no place of its own for the panel, so a small plugin carries it. The product is then sold as a package holding both. [examples/pkg_kitdemotpl](examples/pkg_kitdemotpl) is a template sold this way, and has everything below in it.

1. **Tell the developer first.** The package element in Release Manager has to be the new package (`pkg_x`), with its type set to Package.
2. **Lay out the package:** `pkg_x.xml` and `script.php` at the top, the existing extension unchanged in `packages/tpl_x` (or `lib_x`, `com_x`), and the new plugin in `packages/plg_system_xlicence`.
3. **Create the plugin.** Its element is `xlicence`, and it has only a manifest, `services/provider.php` and an extension class. **Its `<name>` must be the product name exactly,** because the panel and the install notice send customers to it by that name.
4. **Follow sections 3 and 4 for that plugin,** with `--type=plugin --group=system --ext=xlicence --element=pkg_x --product="PRODUCT"`. The update server goes in the package's manifest, and the generated `postflight` in the package's `script.php`.
5. **Turn the plugin on.** Joomla installs plugins disabled, and the panel needs this one enabled. In that `postflight`, when `$type` is `install`, set `enabled = 1` on the plugin's row in `#__extensions`. The example has the lines. Change nothing else on install.
6. **Tell the developer how to build it:** zip the contents of `pkg_x`, so `pkg_x.xml` is at the top of the zip, with forward slashes in the entry names. PowerShell 5.1's `Compress-Archive` doesn't do that.

Customers find the panel under **System → Plugins**. Tell the developer to mention that in their install instructions.

## 7. Check your work

- Run `php -l` on every PHP file you added or changed.
- Check every XML file you edited is well formed.
- Check nothing still contains `{{`, and that no manifest contains `<dlid`.
- If you can run a Joomla site, install the extension there and open the panel. If not, say that you couldn't.

## 8. Report back

Tell the developer, in plain language:

1. What you changed, file by file, and what you added.
2. That they need to build and release a new version for this to reach customers.
3. How to test: install it, open **Link your account**, enter the email of an account with the package's allowed user group, and expect **Licence active**. For a module, the tab is on any copy of the module under **Content → Site Modules**, or **Administrator Modules**. `<temp>/kit/test-link.sh` runs the same calls against the store from the command line. Copy it out if the temp folder is going away.
4. What to set on the store, in the package under Release Manager's **Packages**:
   - **Allowed User Groups**
   - **Renewal URL** and **Sign-Up URL**
   - **Account Link Location**, for example `in Components > PRODUCT > Link your account`, `in System > Plugins > PRODUCT > Link your account` or `in Content > Site Modules > PRODUCT > Link your account`

   The package's **Add licensing to your extension** screen shows which of these are missing.

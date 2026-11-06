# Link your account: {{PRODUCT}}

Generated for **{{PRODUCT}}** on {{STORE_NAME}} ({{STORE_BASE}}). The store URL, package element, namespace and wording are already filled in.

This adds a **Link your account** tab to your module's settings. A customer enters the email they bought with, and paid updates then install from Joomla's own update screen. It takes three steps and changes none of your existing code except your manifest and `script.php`.

## 1. Copy the files

Copy the `src` and `language` folders in this kit into your module's folder. They only add new files:

| File | What it is |
|---|---|
| `src/Helper/AccountLinkHelper.php` | The client: register, link, verify, write the link to your update site |
| `src/Helper/account-panel.php` | The panel's layout. Change it freely |
| `src/Field/AccountlinkField.php` | Shows the panel in the module's settings, and answers it |
| `language/*/{{LANG_EXTENSION}}.ini` | The panel's text, in 17 languages. Change the wording freely |

## 2. Edit your manifest

`snippets/manifest.xml` has the lines, with your URLs in them. Each block says where it goes.

## 3. Restore the link after updates

`snippets/postflight.php` has what goes in `postflight()`.

## Test it

1. Build and install your module, then open it under **{{MODULE_MENU}}**. Any copy of the module will do, and it doesn't have to be published. On the screen for a new copy, the tab works before you save.
2. On the **Link your account** tab, enter the email of an account that has the package's allowed user group. The panel should say **Licence active**.
3. Go to **System → Update Sites**. Your entry's location should now end in `dlid=…`.
4. Upload a higher version to the store, and it should appear under **System → Update → Extensions** and install.

`sh test-link.sh customer@example.com` runs the same calls against your store from the command line, if you want to see the raw answers.

In the package on your store, set **Account Link Location** to `in {{MODULE_MENU_TEXT}} > {{PRODUCT}} > Link your account`. Joomla's update screen uses it to tell a customer where to link.

## Good to know

- **Linking needs no email confirmation, on purpose.** Your code is GPL, so nobody can be stopped from running it. Instead, the owner of the address gets an email naming the domain and can remove or report the site on the store's My Installations page, and you see every domain under **Installations**, so you can write to sites that haven't paid.
- **Your customers never handle a key.** Don't add a `<dlid>` tag to your manifest: Joomla would then show them a "Download Key missing" warning and a box to paste one into.
- **The link belongs to the module, not to one copy of it.** It is stored in the `custom_data` column of the module's row in `#__extensions`, under the key `csrm_account`, so every copy shows the same panel. Saving a copy and updating the module both leave it alone. If your module already keeps data in `custom_data`, it needs to be JSON for the two to live side by side.
- **Keep the security checks** if you change `AccountLinkHelper`: the strict `isOwnFeed()`, the `getDlid()` shape check, and escaping everything the store sends. It is shown to an administrator in your customer's admin.
- **Requires** `<namespace path="src">` in your manifest, on Joomla 5 or 6 and PHP 8.1+. A module without one can add it: `snippets/manifest.xml` has the line, and the module's own code doesn't have to change.

The full guide: https://docs.cybersalt.com/extensions/release-manager/developer-guide

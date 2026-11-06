# Link your account: {{PRODUCT}}

Generated for **{{PRODUCT}}** on {{STORE_NAME}} ({{STORE_BASE}}). The store URL, package element, namespace and wording are already filled in.

This adds a **Link your account** screen to `{{EXT_ELEMENT}}`. A customer enters the email they bought with, and paid updates then install from Joomla's own update screen. It takes three steps and changes none of your existing code except your manifest and `script.php`.

## 1. Copy the files

Copy the `admin` folder in this kit over your component's admin folder. It only adds new files:

| File | What it is |
|---|---|
| `admin/src/Helper/AccountLinkHelper.php` | The client: register, link, verify, write the link to your update site |
| `admin/src/Helper/account-panel.php` | The panel's layout. Change it freely |
| `admin/src/Controller/AccountController.php` | What the panel posts to |
| `admin/src/View/Account/HtmlView.php`, `admin/tmpl/account/default.php` | The screen that shows the panel |
| `admin/language/*/{{LANG_EXTENSION}}.ini` | The panel's text, in 17 languages. Change the wording freely |

If your component's admin folder isn't called `admin` in your repository, copy them to wherever those folders are.

## 2. Edit your manifest

`snippets/manifest.xml` has the lines, with your URLs in them. Each block says where it goes.

## 3. Restore the link after updates

`snippets/postflight.php` has what goes in `postflight()`.

## Test it

1. Build and install your extension, then open **Components → {{PRODUCT}} → Link your account**.
2. Enter the email of an account that has the package's allowed user group. The panel should say **Licence active**.
3. Go to **System → Update Sites**. Your entry's location should now end in `dlid=…`.
4. Upload a higher version to the store, and it should appear under **System → Update → Extensions** and install.

`sh test-link.sh customer@example.com` runs the same calls against your store from the command line, if you want to see the raw answers.

In the package on your store, set **Account Link Location** to `in Components > {{PRODUCT}} > Link your account`. Joomla's update screen uses it to tell a customer where to link.

## Good to know

- **Linking needs no email confirmation, on purpose.** Your code is GPL, so nobody can be stopped from running it. Instead, the owner of the address gets an email naming the domain and can remove or report the site on the store's My Installations page, and you see every domain under **Installations**, so you can write to sites that haven't paid.
- **Your customers never handle a key.** Don't add a `<dlid>` tag to your manifest: Joomla would then show them a "Download Key missing" warning and a box to paste one into.
- **The link is stored in your extension's `custom_data` column** in `#__extensions`, under the key `csrm_account`, not in its params. Saving your Options screen and updating your extension both leave it alone. If your extension already keeps data in `custom_data`, it needs to be JSON for the two to live side by side.
- **Keep the security checks** if you change `AccountLinkHelper`: the strict `isOwnFeed()`, the `getDlid()` shape check, and escaping everything the store sends. It is shown to a Super User in your customer's admin.
- **Requires** a namespaced component with `<namespace path="src">` in its manifest, on Joomla 5 or 6 and PHP 8.1+.

The full guide: https://docs.cybersalt.com/extensions/release-manager/developer-guide

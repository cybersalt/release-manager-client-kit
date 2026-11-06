# Release Manager client kit

Sell a Joomla extension through [Cybersalt Release Manager](https://www.cybersalt.com/extensions/release-manager)? This kit lets your customers link your extension to their account on your store, so your paid updates install from Joomla's own update screen.

**The easiest way to use it is inside Release Manager.** Open **Components → Release Manager → Packages**, and click **Add licensing to your extension** under your package. That screen has everything below with your store's details already filled in.

Your customers never handle a key. They type the email they bought with into your extension, the site registers with your store, and that's it. This kit adds that to your extension, and there are three ways to do it.

## Pick a route

### 1. A generated kit

Release Manager's screen builds a zip of ready-made files with your details in them, or you can run the same generator here:

```sh
php generate.php --store=https://your-store.example --store-name="Your Store" \
    --element=pkg_myextension --type=component --ext=com_myextension \
    --namespace="Acme\Component\MyExtension" --product="My Extension" --out=./kit
```

Then follow the `README.md` it writes into `./kit`. You copy the files in and paste a few lines. Run `php generate.php --help` for every option.

| Your extension | Where customers link |
|---|---|
| Component | A **Link your account** screen in its menu |
| Plugin | A **Link your account** tab in its settings |
| Module | A **Link your account** tab in its settings |
| Anything else, such as a template or a library | The tab of a small plugin sold with it in a package. [INTEGRATE.md](INTEGRATE.md#6-anything-else-add-a-small-plugin) has the steps |

### 2. Let an AI assistant do it

Open your extension's code in Claude Code or a similar coding assistant and say:

> Add licence linking to this Joomla extension by following the instructions at https://raw.githubusercontent.com/cybersalt/release-manager-client-kit/main/INTEGRATE.md
>
> Store URL: https://your-store.example
> Store name: Your Store
> Package element: pkg_myextension
> Feed key: (none)
> Product name: My Extension

Release Manager's screen gives you this prompt with your values in it. [INTEGRATE.md](INTEGRATE.md) tells the assistant what to do and what not to touch, and it reports back what it changed and how to test it.

### 3. Have us do it

All of the above is included with your Release Manager licence. If you'd still rather not touch the code, Cybersalt offers a [fixed-fee integration service](https://www.cybersalt.com/extensions/release-manager-integration-service).

## See it working

[examples/com_kitdemo](examples/com_kitdemo), [examples/plg_system_kitdemoplg](examples/plg_system_kitdemoplg) and [examples/mod_kitdemomod](examples/mod_kitdemomod) are small extensions with the integration finished, so you can see exactly what ends up where. Only their manifests, `script.php` and, for the plugin, one method were edited by hand. Everything else the integration added came straight from the generator. [examples/pkg_kitdemotpl](examples/pkg_kitdemotpl) is a template sold in a package with a small plugin. They all point at `store.example.com`, so generate your own kit rather than copying their files.

## How linking works

Your extension makes up an installation ID for the site, registers it with your store and links the email the customer enters. The store checks that email against your package's user groups and says whether the licence is active. Your extension then writes `<installation_id>:<sha256 of the email>` onto its own update site, and Joomla sends it with every update download. Your customer never sees it.

Linking needs no email confirmation, on purpose. Your extension is GPL, so nobody can be stopped from running it. Instead, the owner of the address gets an email naming the domain and can remove or report the site on your store's My Installations page, and you see every domain under **Installations**, so you can write to sites that haven't paid.

## More

- [Developer guide](https://docs.cybersalt.com/extensions/release-manager/developer-guide)
- [Release Manager documentation](https://docs.cybersalt.com/extensions/release-manager)

Licensed under the GNU General Public License version 2 or later. See [LICENSE](LICENSE).

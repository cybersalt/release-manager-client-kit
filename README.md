# Release Manager client kit

Sell a Joomla extension through [Cybersalt Release Manager](https://www.cybersalt.com/extensions/release-manager)? This kit lets your customers link your extension to their account on your store, so your paid updates install from Joomla's own update screen.

**The easiest way to use it is inside Release Manager.** Open **Components → Release Manager → Packages**, and click **Add licensing to your extension** under your package. That screen has everything below with your store's details already filled in.

## Pick a route

### 1. No code: a download key

Add your update server and one `<dlid>` line to your manifest:

```xml
<updateservers>
    <server type="extension" name="My Extension">https://your-store.example/index.php?option=com_csreleasemanager&amp;task=api.updatexml&amp;format=raw&amp;element=pkg_myextension</server>
</updateservers>
<dlid prefix="dlid=" suffix="" />
```

Your customers log in to your store, add their site on the **My Installations** page, copy its download key, and paste it into your extension's entry under **System → Update Sites**. This works for any extension type, including modules. It needs Release Manager 1.15.0 or later on your store.

### 2. A generated kit: a Link your account screen

Customers type their email into your extension instead. Release Manager's screen builds a zip of ready-made files with your details in them, or you can run the same generator here:

```sh
php generate.php --store=https://your-store.example --store-name="Your Store" \
    --element=pkg_myextension --type=component --ext=com_myextension \
    --namespace="Acme\Component\MyExtension" --product="My Extension" --out=./kit
```

Then follow the `README.md` it writes into `./kit`. You copy the files in and paste a few lines. Works for components and plugins. Run `php generate.php --help` for every option.

### 3. Let an AI assistant do it

Open your extension's code in Claude Code or a similar coding assistant and say:

> Add licence linking to this Joomla extension by following the instructions at https://raw.githubusercontent.com/cybersalt/release-manager-client-kit/main/INTEGRATE.md
>
> Store URL: https://your-store.example
> Store name: Your Store
> Package element: pkg_myextension
> Feed key: (none)
> Product name: My Extension

Release Manager's screen gives you this prompt with your values in it. [INTEGRATE.md](INTEGRATE.md) tells the assistant what to do and what not to touch, and it reports back what it changed and how to test it.

### 4. Have us do it

All of the above is included with your Release Manager licence. If you'd still rather not touch the code, Cybersalt offers a [fixed-fee integration service](https://www.cybersalt.com/extensions/release-manager/integration-service).

## See it working

[examples/com_kitdemo](examples/com_kitdemo) and [examples/plg_system_kitdemoplg](examples/plg_system_kitdemoplg) are small extensions with the integration finished, so you can see exactly what ends up where. Only their manifests, `script.php` and, for the plugin, one method were edited by hand. Everything else the integration added came straight from the generator. They point at `store.example.com`, so generate your own kit rather than copying their files.

## How linking works

Your extension makes up an installation ID for the site, registers it with your store and links the email the customer enters. The store checks that email against your package's user groups and says whether the licence is active. Your extension then writes a download key, `<installation_id>:<sha256 of the email>`, onto its own update site, and Joomla sends it with every update download.

Linking needs no email confirmation, on purpose. Your extension is GPL, so nobody can be stopped from running it. Instead, the owner of the address gets an email naming the domain and can remove or report the site on your store's My Installations page, and you see every domain under **Installations**, so you can write to sites that haven't paid.

## More

- [Developer guide](https://docs.cybersalt.com/extensions/release-manager/developer-guide)
- [Release Manager documentation](https://docs.cybersalt.com/extensions/release-manager)

Licensed under the GNU General Public License version 2 or later. See [LICENSE](LICENSE).

# WordPress Block Invokers

[![Commit activity](https://img.shields.io/github/commit-activity/m/swissspidy/block-invokers)](https://github.com/swissspidy/block-invokers/pulse/monthly)
[![Code Coverage](https://codecov.io/gh/swissspidy/block-invokers/branch/main/graph/badge.svg)](https://codecov.io/gh/swissspidy/block-invokers)
[![License](https://img.shields.io/github/license/swissspidy/block-invokers)](https://github.com/swissspidy/block-invokers/blob/main/LICENSE)

Experimenting with the [Invoker Commands API](https://developer.mozilla.org/en-US/docs/Web/API/Invoker_Commands_API).

## Demo

This allows the button block to control other blocks on your site:

![Commands Panel](https://github.com/user-attachments/assets/8ab90fcf-b1a4-4d29-a307-d056081c8c03)

https://github.com/user-attachments/assets/74f83e35-18e1-4bfb-bd4c-0764de539860

## Try it

Try it now in your browser:

[![Test on WordPress Playground](https://img.shields.io/badge/Test%20on%20WordPress%20Playground-3F57E1?style=for-the-badge&logo=WordPress&logoColor=ffffff)](https://playground.wordpress.net/?mode=seamless&blueprint-url=https://raw.githubusercontent.com/swissspidy/block-invokers/main/blueprints/playground.json)

Or install and activate the latest nightly build on your own WordPress website:

[![Download latest nightly build](https://img.shields.io/badge/Download%20latest%20nightly-24282D?style=for-the-badge&logo=Files&logoColor=ffffff)](https://swissspidy.github.io/block-invokers/nightly.zip)

## How it works

The button block gets new `command` and `commandFor` attributes, mirroring the
[`command`](https://html.spec.whatwg.org/multipage/form-elements.html#attr-button-command) and
[`commandfor`](https://html.spec.whatwg.org/multipage/form-elements.html#attr-button-commandfor)
HTML attributes. Blocks that can be controlled by a button (details, video, audio, image)
expose the commands they support, and the editor UI lets you wire a button up to one of them.

### Spec status

The [Invoker Commands API](https://developer.mozilla.org/en-US/docs/Web/API/Invoker_Commands_API)
(`command`/`commandfor` and `CommandEvent`) is part of the HTML spec and supported in
Chrome 135+, Firefox 144+, and Safari 26.2+.
The [invokers-polyfill](https://github.com/keithamus/invokers-polyfill) is loaded on the
frontend for older browsers whenever a button uses commands.

Only the popover and dialog commands (`toggle-popover`, `show-popover`, `hide-popover`,
`show-modal`, `close`, `request-close`) are built into HTML so far. The commands for
`<details>` and media elements used by this plugin are still part of the
[Open UI future invokers proposal](https://open-ui.org/components/future-invokers.explainer/),
so this plugin uses custom (`--`-prefixed) commands — which _are_ part of the spec — and
implements their proposed behavior itself:

| Block   | Commands                                             |
| ------- | ---------------------------------------------------- |
| Details | `--toggle`, `--open`, `--close`                      |
| Video   | `--play-pause`, `--play`, `--pause`, `--toggle-muted` |
| Audio   | `--play-pause`, `--play`, `--pause`, `--toggle-muted` |
| Image   | `--show-lightbox`, `--hide-lightbox`                 |

Once those proposals land in the HTML spec and in browsers, the plugin can switch to the
built-in command names.

## Development

Requirements: Node.js 20+, PHP 8.0+, Composer, and Docker (for `wp-env`).

```bash
npm install
composer install

# Build the plugin.
npm run build

# Linting.
npm run lint:js
composer lint

# JS unit tests.
npm run test:unit

# PHP unit tests.
npm run wp-env start
./node_modules/.bin/wp-env run tests-cli --env-cwd=wp-content/plugins/block-invokers vendor/bin/phpunit

# E2E tests.
npm run wp-env start
npm run test:e2e
```

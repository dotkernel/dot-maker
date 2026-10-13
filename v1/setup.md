# Setup

Once installed, `dot-maker` is ready for usage, no need for extra configurations.

## Add dot-maker to composer.json

> This step is optional but recommended.

Open your project's `composer.json` and locate the `scripts` section.
If it does not exist, create it at the document's root level.

Register a new script by appending `"alias": "dot-maker"` to the `scripts` section, where **alias** can be any string you want; like, for example, **make**.

```json
{
    "scripts": {
        "make": "dot-maker"
    }
}
```

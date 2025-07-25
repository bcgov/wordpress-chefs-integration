# WordPress CHEFS Integration Plugin

## Getting started
1. Copy sample.env and rename it .env.
2. Add form id of your producer form as `PRODUCER_FORM_ID`.
   1. This can be found in the URL when viewing the form in CHEFS.
3. Add form API key of your producer form as `PRODUCER_FORM_API_KEY`.
   1. This must be generated in CHEFS for your form under Api key > Generate API Key.

## Map file structure
Currently there is one hardcoded partial mapping file (`maps/producer.json`) for BC Food Directory Producer CPT posts. In the future this map might be provided some other way (plugin options, env file, etc.) so that this plugin can be reused for other sites that need CHEFS integration. The producer map is included for demonstration purposes.

Map files are json objects that map CHEFS field ids to either WordPress metadata keys or WordPress taxonomies. The top level key should be the CHEFS field id (`<chefs field id>` in the example below). Then each child object should have some or all of these properties:
- `name`: `string`. Either the metadata key or the taxonomy slug the CHEFS field should map to. Required.
- `type`: `string`. Either `meta_input` or `tax_input` depending on whether it's metadata or a taxonomy. Default: `meta_input`.
- `is_title`: `boolean`. If set to `true`, the value will be used for the post's `post_title`. This can only be used on meta_input fields. Default: `false`.

Example:
```json
{
    "<chefs field id>": {
        "name": "<wordpress meta key>",
        "type": "meta_input",
        "is_title": true
    },
    "<chefs field id>": {
        "name": "<wordpress taxonomy slug>",
        "type": "tax_input"
    }
    ...
}
```
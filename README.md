# WordPress CHEFS Integration Plugin

## Getting started

### Environment Setup

1. **Create environment file**: Copy `sample.env` and rename it to `.env`

2. **Configure Producer Form ID**:
   - Add your producer form ID as `PRODUCER_FORM_ID` in the `.env` file
   - You can find this ID in the URL when viewing the form in CHEFS

3. **Set up API Key**:
   - Add your producer form API key as `PRODUCER_FORM_API_KEY` in the `.env` file
   - Generate this key in CHEFS: navigate to your form → **Api key** → **Generate API Key**

## Map File Structure

### Overview

Map files are JSON objects that define how CHEFS form fields map to WordPress data. Currently, there's a hardcoded mapping file at `maps/producer.json` for BC Food Directory Producer CPT posts (included for demonstration purposes).

> **Note**: In future versions, mapping configuration may be provided through plugin options or environment files to improve reusability across different sites.

### Map File Format

Each map file uses **CHEFS field IDs** as top-level keys, with mapping configuration objects as values.

#### Configuration Properties

| Property | Type | Description | Required | Default |
|----------|------|-------------|----------|---------|
| `name` | `string` | WordPress metadata key or taxonomy slug | ✅ Yes | - |
| `type` | `string` | Data type: `meta_input` (metadata) or `tax_input` (taxonomy) | No | `meta_input` |
| `is_title` | `boolean` | Use this field's value as the post title (metadata only) | No | `false` |

### Example Configuration

```json
{
  "field_123_company_name": {
    "name": "company_name",
    "type": "meta_input",
    "is_title": true
  },
  "field_456_business_category": {
    "name": "business_category",
    "type": "tax_input"
  },
  "field_789_description": {
    "name": "company_description",
    "type": "meta_input"
  }
}
```

**Explanation:**

- `field_123_company_name` → WordPress metadata `company_name` + used as post title
- `field_456_business_category` → WordPress taxonomy `business_category`
- `field_789_description` → WordPress metadata `company_description`

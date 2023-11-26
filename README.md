# Schema Enhancements for RankMath

Boost your WordPress SEO effortlessly with Schema Enhancements, the key to unlocking richer search results and higher visibility.

## Features

- **Person Schema**: Augment `Person` schema with comprehensive attributes to enhance your E-E-A-T profile.
- **FAQ Schema**: Seamlessly generate `FAQPage` schema from ACF repeater fields for direct answers in search results.

## Upcoming Enhancements

- `JobPosting`
- `Event`
- `Course`

## Quick Start

1. Clone this repository into your WordPress theme's directory.
2. Add the class to your theme's `functions.php`:

```php
require_once get_template_directory() . '/inc/schema-enhancements.php';
new SchemaEnhancements();
```

3. Import the "acf-schema-enhancements.json" file into your Advanced Custom Fields under tools > import to add the necessary fields into the user profiles (for the person enhancements) and posts/pages (for the FAQ enhancements and future JobPosting, Event and Course).

## Get Help

- Reach out on [Twitter](https://twitter.com/jcvangent)
- Open an [issue on GitHub](https://github.com/hansvangent/rank-math-schema-enhancements/issues/new)

## Contribute

#### Issues

For a bug report, bug fix, or a suggestion, please feel free to open an issue.

#### Pull request

Pull requests are always welcome, and I'll do my best to do reviews as quickly as possible.

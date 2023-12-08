# Schema Enhancements for RankMath

Boost your WordPress SEO effortlessly with Schema Enhancements, the key to unlocking richer search results and higher visibility.

## Features

- **Person Schema**: Augment `Person` schema with comprehensive attributes to enhance your E-E-A-T profile (jobTitle/honoricPrefix/honoricSuffix/knowsAbout/alumniOf/Awards).
- **FAQ Schema**: Seamlessly generate `FAQPage` schema from ACF repeater fields for direct answers in search results.
- **JobPosting Schema**: Optimize job listings with detailed information like employment type, location, and qualifications, so you can get featured directly in Google for Jobs.

## Upcoming Enhancements

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

4. After adding content to the user profiles on your website, the schema markup will automatically be added to the author archive page and to the posts and pages created by that author. If you want to show the content of those fields on the author archive page in HTML, you can do so by using the following code example:

```php
$honorificPrefix = get_field('PersonhonorificPrefix', 'user_' . $author_id);
if (!empty($honorificPrefix)) {
	echo esc_html($honorificPrefix) . ' ';
}

echo esc_html(get_the_author_meta('display_name', $author_id));

$honorificSuffix = get_field('PersonhonorificSuffix', 'user_' . $author_id);
if (!empty($honorificSuffix)) {
	echo ' ' . esc_html($honorificSuffix);
}
```

To add the full name of the author and the potential 'honorificPrefix' and 'honorificSuffix.' The various field names are located in your WordPress backend ACF > Field Groups > Author E-E-A-T Schema.

## Get Help

- Reach out on [Twitter](https://twitter.com/jcvangent)
- Open an [issue on GitHub](https://github.com/hansvangent/rank-math-schema-enhancements/issues/new)

## Contribute

#### Issues

For a bug report, bug fix, or a suggestion, please feel free to open an issue.

#### Pull request

Pull requests are always welcome, and I'll do my best to do reviews as quickly as possible.

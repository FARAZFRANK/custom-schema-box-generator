=== Custom Schema Box Generator ===
Contributors: farazfrank
Donate link: https://paypal.me/buymecupofcoffee?locale.x=en_GB&country.x=IN
Tags: schema, json-ld, seo, structured data, rich snippets
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 2.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add JSON-LD structured data (Schema.org markup) to your WordPress site. Improve SEO and get rich results in Google search!

== Description ==

**Custom Schema Box Generator** is a powerful yet user-friendly WordPress plugin that helps you add JSON-LD structured data (Schema.org markup) to your website. Improve your SEO, get rich snippets in Google search results, and help search engines better understand your content.

= 🌟 Key Features =

* **20+ Pre-built Schema Templates** - Ready-to-use templates for Article, Product, Local Business, FAQ, Recipe, Event, Video, and more
* **Dynamic Placeholders** - Automatically populate schemas with post data (title, author, date, image, site name, categories, etc.)
* **Individual & Dynamic Modes** - Choose between unique schema per post or one common schema for all posts
* **Granular Control** - Enable/disable schemas per post type and individual items
* **Custom Post Types Support** - Works with any custom post type
* **One-Click Copy** - Copy templates to clipboard instantly
* **Bulk Operations** - Select All/Deselect All buttons for efficiency
* **Comprehensive Guide** - Step-by-step instructions and FAQ section
* **Beautiful UI** - Modern, intuitive, and responsive interface
* **WordPress Standards** - Clean, secure, and optimized code
* **100% Free** - No ads, no upsells, completely open-source

= 📋 Available Schema Types =

* Article - For blog posts and articles
* Product - For e-commerce products
* Local Business - For physical businesses
* FAQ - For FAQ pages
* How-To - For step-by-step guides
* Recipe - For cooking recipes
* Event - For events and conferences
* Video - For video content
* Organization - For company information
* Breadcrumb - For navigation breadcrumbs
* Review - For product/service reviews
* Course - For online courses
* Job Posting - For job listings
* Book - For books and publications
* Software - For software and apps
* Restaurant - For restaurants and cafes
* Music - For songs and albums
* Podcast - For podcast episodes
* News Article - For news articles
* Medical - For medical information

= 🔧 Dynamic Placeholders =

Use these placeholders to automatically populate your schemas:

* `{{post_title}}` - Post/Page Title
* `{{post_excerpt}}` - Post Excerpt
* `{{post_date}}` - Publication Date (ISO 8601)
* `{{post_modified}}` - Last Modified Date (ISO 8601)
* `{{author_name}}` - Author Display Name
* `{{author_url}}` - Author Archive URL
* `{{featured_image}}` - Featured Image URL
* `{{post_url}}` - Post Permalink
* `{{site_name}}` - Website Name
* `{{site_url}}` - Website URL
* `{{site_description}}` - Website Tagline
* `{{post_category}}` - Post Categories (comma-separated)
* `{{post_category_first}}` - First Post Category
* `{{post_tags}}` - Post Tags (comma-separated)
* `{{post_id}}` - Post ID

= 🎯 Perfect For =

* **Bloggers** - Add Article schema to improve visibility
* **E-commerce Sites** - Add Product schema with pricing and reviews
* **Local Businesses** - Add LocalBusiness schema for local SEO
* **Content Creators** - Add Video, Recipe, or HowTo schemas
* **News Sites** - Add NewsArticle schema for news content
* **Educational Sites** - Add Course schema for online courses
* **Job Boards** - Add JobPosting schema for job listings

= 🚀 How It Works =

1. **Enable Schema** - Go to Settings → Schema Box Generator and enable for post types
2. **Choose Mode** - Select Individual (unique per post) or Dynamic (common for all)
3. **Add Schema** - Use templates with placeholders or write custom JSON-LD
4. **Test & Publish** - Validate with Google Rich Results Test and publish

= 🔄 Individual vs Dynamic Mode =

**Individual Mode:**
* Add unique schema to each post/page separately
* Best for content with different schema types
* Select specific posts/pages where schema appears
* More control but requires manual setup

**Dynamic Mode:**
* One schema template applies to all posts of that type
* Perfect for blogs with many posts
* Uses placeholders to auto-populate data
* Saves time - no need to add schema to each post
* Recommended for sites with 50+ posts

= 🔍 SEO Benefits =

* Get rich snippets in Google search results
* Show star ratings, images, prices, and more
* Improve click-through rates by up to 30%
* Help search engines understand your content
* Enable voice search optimization
* Support for Google Assistant and Siri

= 🛠️ Developer Friendly =

* Clean, well-documented code
* WordPress coding standards compliant
* Hooks and filters for customization
* Translation-ready
* Compatible with all themes and plugins

= 📚 Documentation =

The plugin includes comprehensive documentation:

* **Settings Tab** - Configure post types and items
* **Schema Templates Tab** - Browse and copy 20+ templates
* **How to Use Tab** - Step-by-step guide
* **FAQ Tab** - Answers to common questions
* **Donate Tab** - Support plugin development

= 🌍 Compatibility =

* Works with Yoast SEO, Rank Math, All in One SEO
* Compatible with WooCommerce
* Works with Advanced Custom Fields (ACF)
* Compatible with Elementor, Divi, Beaver Builder
* Works with all WordPress themes
* Compatible with all major hosting providers

= 💝 Support the Plugin =

If this plugin has helped you, please consider:

* Rating it 5 stars on WordPress.org
* Buying me a coffee via PayPal
* Sharing it with others
* Reporting bugs and suggesting features

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Go to Plugins → Add New
3. Search for "Custom Schema Box Generator"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin ZIP file
2. Go to Plugins → Add New → Upload Plugin
3. Choose the ZIP file and click "Install Now"
4. Click "Activate Plugin"

= FTP Installation =

1. Download and extract the plugin ZIP file
2. Upload the `custom-schema-box-generator` folder to `/wp-content/plugins/`
3. Activate the plugin through the Plugins menu in WordPress

== Frequently Asked Questions ==

= What is Schema Markup? =

Schema markup is structured data that helps search engines understand your content better. It can result in rich snippets in search results, showing additional information like ratings, images, prices, and more.

= What is JSON-LD? =

JSON-LD (JavaScript Object Notation for Linked Data) is the recommended format by Google for adding structured data to web pages. It's easy to implement and doesn't affect your page's HTML.

= Do I need coding knowledge? =

No! The plugin provides 20+ pre-built templates that you can copy and customize. Just replace the placeholder values with your own information.

= How do I test if my schema is working? =

Use Google's Rich Results Test tool: https://search.google.com/test/rich-results
Enter your page URL and it will show if your schema is valid and what rich results you're eligible for.

= Will this plugin slow down my site? =

No! The plugin is highly optimized and adds minimal overhead. It only loads assets on admin pages and outputs lightweight JSON-LD code on the frontend.

= Can I use multiple schemas on one page? =

Currently, you can add one schema per page/post. Support for multiple schemas is planned for a future update.

= Does it work with custom post types? =

Yes! The plugin automatically detects all registered custom post types and allows you to enable schema for them.

= Is it compatible with other SEO plugins? =

Yes! It works alongside Yoast SEO, Rank Math, All in One SEO, and other popular SEO plugins without conflicts.

= Can I use it with WooCommerce? =

Yes! You can add Product schema to your WooCommerce products. Automatic WooCommerce integration is planned for a future update.

= How do I get support? =

For support, please visit the plugin's support forum on WordPress.org or open an issue on GitHub.

= Is it free? =

Yes! The plugin is 100% free and open-source with no ads, no upsells, and no premium versions.

= Can I contribute? =

Absolutely! Contributions are welcome. You can report bugs, suggest features, submit pull requests, or help with documentation.

== Screenshots ==

1. Settings page with granular control over post types and items
2. Schema Templates library with 20+ pre-built templates
3. Meta box for adding custom schema to posts/pages
4. How to Use guide with step-by-step instructions
5. FAQ section with answers to common questions
6. Template card with copy and view code buttons
7. Debug mode showing custom post type detection
8. Donate page to support plugin development

== Changelog ==

= 2.3.0 (2025-01-16) =
* **WordPress.org Compliance Update** - Fixed all plugin review issues for WordPress.org submission
* Security: Removed JSON_UNESCAPED_SLASHES and JSON_UNESCAPED_UNICODE flags from wp_json_encode() to prevent XSS vulnerabilities
* Security: All JSON output now properly escaped following WordPress security standards
* Fixed: Replaced all remote file URLs (https://example.com) in schema templates with dynamic placeholders
* Fixed: Changed menu slug from 'custom-schema-box-generator' to 'csgbxgen-settings' for unique prefix compliance
* Improved: HowTo template now uses {{featured_image}} placeholder instead of example URLs
* Improved: Video template uses YOUR_VIDEO_URL and YOUR_EMBED_URL placeholders for user customization
* Improved: BreadcrumbList template uses {{site_url}} and YOUR_CATEGORY_URL placeholders
* Improved: JobPosting template uses {{site_name}}, {{site_url}}, and {{site_logo}} placeholders
* Improved: PodcastEpisode template uses YOUR_PODCAST_URL and YOUR_EPISODE_MP3_URL placeholders
* Improved: NewsArticle template uses {{site_name}} and {{site_logo}} placeholders
* Added: {{site_logo}} placeholder for WordPress theme custom logo URL
* Added: Documentation note clarifying that example outputs are for illustration purposes only
* Enhanced: All schema templates now use dynamic placeholders or clear user-replaceable text
* Enhanced: Better security with proper WordPress escaping throughout
* Code Quality: Follows WordPress coding standards and security best practices
* Code Quality: No external dependencies or remote file calls
* Compatibility: Ready for WordPress.org plugin directory approval

= 2.2.0 (2025-01-15) =
* Added 10 new schema templates (Review, Course, Job Posting, Book, Software, Restaurant, Music, Podcast, News Article, Medical)
* Added "(no title)" display for posts without titles
* Enhanced template descriptions
* Improved code organization
* Updated documentation

= 2.1.0 (2025-01-14) =
* Added Donate tab with PayPal integration
* Improved responsive design for mobile devices
* Enhanced CSS animations and transitions
* Added floating coffee icon animation
* Updated version numbering system

= 2.0.0 (2025-01-13) =
* Major UI redesign with tabbed navigation
* Added Schema Templates Library (10 initial templates)
* Added comprehensive How to Use guide
* Added FAQ section with 13+ questions
* Added template copy functionality with clipboard API
* Added modal for viewing template code
* Complete CSS overhaul with modern design
* Added responsive grid layout for templates
* Improved user experience significantly

= 1.0.2 (2025-01-12) =
* Fixed CSS display issues with list items
* Added Select All/Deselect All buttons
* Moved inline CSS to external file
* Created separate JavaScript file
* Enhanced button state management
* Improved checkbox list styling
* Added flexbox layout for vertical stacking

= 1.0.1 (2025-01-11) =
* Fixed custom post type detection
* Added debug mode for troubleshooting
* Improved settings page layout
* Enhanced post type section styling
* Fixed CPT items not showing issue

= 1.0.0 (2025-01-10) =
* Initial release
* Basic schema functionality
* Meta box for posts and pages
* Settings page with enable/disable options
* JSON validation
* WordPress coding standards compliance

== Upgrade Notice ==

= 2.3.0 =
Critical security and compliance update! Fixes all WordPress.org plugin review issues including JSON encoding security, removes remote file URLs, and implements unique prefix naming. Required for WordPress.org submission. Highly recommended upgrade for all users.

= 2.2.0 =
Major update with 10 new schema templates! Now includes Review, Course, Job Posting, Book, Software, Restaurant, Music, Podcast, News Article, and Medical schemas. Also adds "(no title)" display for posts without titles.

= 2.0.0 =
Major update with complete UI redesign! New tabbed interface, 10 schema templates, comprehensive guide, and FAQ section. Highly recommended upgrade.

= 1.0.2 =
Important bug fixes for CSS display issues. Adds Select All/Deselect All buttons for better usability.

== Additional Info ==

**Requirements:**
* WordPress 5.0 or higher
* PHP 7.4 or higher
* MySQL 5.6 or higher

**Links:**
* [Plugin Homepage](https://wordpress.org/plugins/custom-schema-box-generator)
* [Documentation](https://wordpress.org/plugins/custom-schema-box-generator/#installation)
* [Support Forum](https://wordpress.org/support/plugin/custom-schema-box-generator)
* [GitHub Repository](https://github.com/farazfrank/custom-schema-box-generator)
* [Donate](https://paypal.me/buymecupofcoffee?locale.x=en_GB&country.x=IN)

**Credits:**
Developed with ❤️ by WP FRANK

**License:**
This plugin is licensed under the GPL v2 or later.


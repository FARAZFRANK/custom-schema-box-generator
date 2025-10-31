# Contributing to Custom Schema Box Generator

Thank you for your interest in contributing to Custom Schema Box Generator! This document provides guidelines and instructions for contributing to the project.

## 🤝 How to Contribute

There are many ways to contribute to this project:

1. **Report Bugs** - Help us identify and fix issues
2. **Suggest Features** - Share your ideas for improvements
3. **Submit Pull Requests** - Contribute code
4. **Improve Documentation** - Help others understand the plugin
5. **Translate** - Make the plugin available in more languages
6. **Test** - Help test new features and releases
7. **Spread the Word** - Tell others about the plugin

## 🐛 Reporting Bugs

Before reporting a bug, please:

1. **Search existing issues** - Check if the bug has already been reported
2. **Test with default theme** - Verify the issue isn't theme-specific
3. **Disable other plugins** - Check if there's a plugin conflict
4. **Check requirements** - Ensure you meet minimum requirements (WP 5.0+, PHP 7.4+)

### Bug Report Template

```markdown
**Describe the bug**
A clear and concise description of what the bug is.

**To Reproduce**
Steps to reproduce the behavior:
1. Go to '...'
2. Click on '...'
3. Scroll down to '...'
4. See error

**Expected behavior**
A clear and concise description of what you expected to happen.

**Screenshots**
If applicable, add screenshots to help explain your problem.

**Environment:**
- WordPress Version: [e.g. 6.4]
- Plugin Version: [e.g. 2.2.0]
- PHP Version: [e.g. 8.1]
- Theme: [e.g. Twenty Twenty-Four]
- Other Plugins: [list relevant plugins]

**Additional context**
Add any other context about the problem here.
```

## 💡 Suggesting Features

We welcome feature suggestions! Please:

1. **Check existing issues** - See if it's already been suggested
2. **Be specific** - Clearly describe the feature and its benefits
3. **Provide examples** - Show how it would work
4. **Consider scope** - Keep it relevant to the plugin's purpose

### Feature Request Template

```markdown
**Is your feature request related to a problem?**
A clear and concise description of what the problem is.

**Describe the solution you'd like**
A clear and concise description of what you want to happen.

**Describe alternatives you've considered**
A clear and concise description of any alternative solutions or features you've considered.

**Use case**
Describe how this feature would be used and who would benefit from it.

**Additional context**
Add any other context or screenshots about the feature request here.
```

## 🔧 Development Setup

### Prerequisites

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Node.js and npm (for development tools)
- Git
- Code editor (VS Code recommended)

### Local Development

1. **Clone the repository**
```bash
git clone https://github.com/farazfrank/custom-schema-box-generator.git
cd custom-schema-box-generator
```

2. **Install in WordPress**
```bash
# Copy to WordPress plugins directory
cp -r custom-schema-box-generator /path/to/wordpress/wp-content/plugins/
```

3. **Activate the plugin**
- Go to WordPress Admin → Plugins
- Find "Custom Schema Box Generator"
- Click "Activate"

### Development Tools

**Recommended VS Code Extensions:**
- PHP Intelephense
- WordPress Snippets
- ESLint
- Prettier
- EditorConfig

**Recommended Tools:**
- [WP-CLI](https://wp-cli.org/) - WordPress command line
- [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) - Code standards
- [PHPCS WordPress Standards](https://github.com/WordPress/WordPress-Coding-Standards)

## 📝 Coding Standards

### PHP Standards

Follow [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/):

- Use tabs for indentation
- Use single quotes for strings (unless interpolating)
- Add spaces around operators
- Use meaningful variable names
- Add PHPDoc comments for functions
- Escape all output
- Sanitize all input
- Verify nonces for forms

**Example:**
```php
/**
 * Get schema templates.
 *
 * @return array Array of schema templates.
 */
function csg_get_schema_templates() {
    $templates = array(
        'article' => array(
            'name' => 'Article',
            'type' => 'Article',
        ),
    );
    
    return $templates;
}
```

### JavaScript Standards

Follow [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/):

- Use tabs for indentation
- Use single quotes for strings
- Use jQuery for DOM manipulation
- Add comments for complex logic
- Use meaningful variable names

**Example:**
```javascript
jQuery(document).ready(function($) {
    // Handle button click
    $('.csg-copy-template').on('click', function(e) {
        e.preventDefault();
        var templateId = $(this).data('template-id');
        // Copy template logic
    });
});
```

### CSS Standards

Follow [WordPress CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/):

- Use tabs for indentation
- One selector per line
- Properties in alphabetical order
- Use shorthand when possible
- Add comments for sections

**Example:**
```css
/* Template Cards */
.csg-template-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
}
```

## 🔍 Testing

### Manual Testing

Before submitting a pull request:

1. **Test all functionality** - Ensure your changes work as expected
2. **Test on different browsers** - Chrome, Firefox, Safari, Edge
3. **Test on different screen sizes** - Desktop, tablet, mobile
4. **Test with different themes** - Default theme and popular themes
5. **Test with other plugins** - Check for conflicts
6. **Test error cases** - Try to break your code

### Automated Testing

We plan to add automated testing in the future:
- PHPUnit for PHP unit tests
- Jest for JavaScript tests
- Cypress for end-to-end tests

## 📤 Submitting Pull Requests

### Before Submitting

1. **Create an issue first** - Discuss the change before coding
2. **Fork the repository** - Work on your own fork
3. **Create a feature branch** - Don't work on main/master
4. **Follow coding standards** - Use PHPCS to check
5. **Test thoroughly** - Ensure everything works
6. **Update documentation** - Update README if needed

### Pull Request Process

1. **Create a feature branch**
```bash
git checkout -b feature/your-feature-name
```

2. **Make your changes**
- Write clean, well-documented code
- Follow coding standards
- Test thoroughly

3. **Commit your changes**
```bash
git add .
git commit -m "Add feature: description of your feature"
```

Use clear commit messages:
- `Add:` for new features
- `Fix:` for bug fixes
- `Update:` for updates to existing features
- `Remove:` for removed features
- `Refactor:` for code refactoring

4. **Push to your fork**
```bash
git push origin feature/your-feature-name
```

5. **Create a pull request**
- Go to the original repository
- Click "New Pull Request"
- Select your branch
- Fill out the PR template
- Submit the PR

### Pull Request Template

```markdown
**Description**
Brief description of what this PR does.

**Related Issue**
Fixes #123

**Type of Change**
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

**Testing**
Describe how you tested your changes.

**Checklist**
- [ ] My code follows the WordPress coding standards
- [ ] I have tested my changes thoroughly
- [ ] I have updated the documentation
- [ ] I have added comments to complex code
- [ ] My changes generate no new warnings
- [ ] I have checked for conflicts with other plugins
```

## 🌍 Translation

Help translate the plugin into your language:

1. **Use Poedit** or similar tool
2. **Create `.po` and `.mo` files**
3. **Place in `/languages/` directory**
4. **Name format:** `custom-schema-box-generator-{locale}.mo`
5. **Submit via pull request**

## 📚 Documentation

Help improve documentation:

- Fix typos and grammar
- Add examples
- Clarify confusing sections
- Add screenshots
- Create video tutorials
- Write blog posts

## 🎨 Design

Help improve the UI/UX:

- Suggest design improvements
- Create mockups
- Improve accessibility
- Enhance mobile experience
- Add animations

## 💬 Community

- Be respectful and inclusive
- Help others in the support forum
- Share your use cases
- Provide constructive feedback
- Celebrate successes

## 📜 Code of Conduct

### Our Pledge

We pledge to make participation in our project a harassment-free experience for everyone, regardless of age, body size, disability, ethnicity, gender identity and expression, level of experience, nationality, personal appearance, race, religion, or sexual identity and orientation.

### Our Standards

**Positive behavior:**
- Using welcoming and inclusive language
- Being respectful of differing viewpoints
- Gracefully accepting constructive criticism
- Focusing on what is best for the community
- Showing empathy towards others

**Unacceptable behavior:**
- Trolling, insulting/derogatory comments, and personal attacks
- Public or private harassment
- Publishing others' private information
- Other conduct which could reasonably be considered inappropriate

## 📧 Contact

- **GitHub Issues:** For bugs and features
- **Support Forum:** For general questions
- **Email:** For private matters

## 🙏 Recognition

Contributors will be:
- Listed in the CONTRIBUTORS.md file
- Mentioned in release notes
- Credited in the plugin

## 📄 License

By contributing, you agree that your contributions will be licensed under the GPL v2 or later license.

---

**Thank you for contributing to Custom Schema Box Generator!** 🎉

Your contributions help make this plugin better for everyone in the WordPress community.


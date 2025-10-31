# Translation Guide for Custom Schema Box Generator

Thank you for your interest in translating Custom Schema Box Generator!

## 📋 Translation Files

This directory contains translation files for the plugin:

- **custom-schema-box-generator.pot** - Template file (do not edit directly)
- **custom-schema-box-generator-{locale}.po** - Translation source files
- **custom-schema-box-generator-{locale}.mo** - Compiled translation files

## 🌍 How to Translate

### Method 1: Using Poedit (Recommended)

1. **Download Poedit**
   - Visit: https://poedit.net/
   - Download and install for your operating system

2. **Open the POT file**
   - Launch Poedit
   - Click "Create New Translation"
   - Select `custom-schema-box-generator.pot`
   - Choose your language

3. **Translate strings**
   - Translate each string in the list
   - Poedit will show context and source references
   - Save your work frequently

4. **Save translation files**
   - Save as: `custom-schema-box-generator-{locale}.po`
   - Poedit will automatically create the `.mo` file
   - Example: `custom-schema-box-generator-es_ES.po` for Spanish (Spain)

5. **Upload files**
   - Place both `.po` and `.mo` files in this directory
   - Activate the translation in WordPress

### Method 2: Using WordPress.org Translation Platform

1. Visit the plugin page on WordPress.org
2. Go to the "Translate" tab
3. Select your language
4. Translate strings online
5. Translations will be automatically available

### Method 3: Manual Translation

1. Copy `custom-schema-box-generator.pot` to `custom-schema-box-generator-{locale}.po`
2. Edit the `.po` file with a text editor
3. Translate the `msgstr` values
4. Compile to `.mo` using `msgfmt` command:
   ```bash
   msgfmt custom-schema-box-generator-es_ES.po -o custom-schema-box-generator-es_ES.mo
   ```

## 🗂️ Locale Codes

Common locale codes:

| Language | Locale Code | File Name |
|----------|-------------|-----------|
| Spanish (Spain) | es_ES | custom-schema-box-generator-es_ES.po |
| French (France) | fr_FR | custom-schema-box-generator-fr_FR.po |
| German | de_DE | custom-schema-box-generator-de_DE.po |
| Italian | it_IT | custom-schema-box-generator-it_IT.po |
| Portuguese (Brazil) | pt_BR | custom-schema-box-generator-pt_BR.po |
| Portuguese (Portugal) | pt_PT | custom-schema-box-generator-pt_PT.po |
| Russian | ru_RU | custom-schema-box-generator-ru_RU.po |
| Chinese (Simplified) | zh_CN | custom-schema-box-generator-zh_CN.po |
| Chinese (Traditional) | zh_TW | custom-schema-box-generator-zh_TW.po |
| Japanese | ja | custom-schema-box-generator-ja.po |
| Korean | ko_KR | custom-schema-box-generator-ko_KR.po |
| Arabic | ar | custom-schema-box-generator-ar.po |
| Dutch | nl_NL | custom-schema-box-generator-nl_NL.po |
| Polish | pl_PL | custom-schema-box-generator-pl_PL.po |
| Turkish | tr_TR | custom-schema-box-generator-tr_TR.po |
| Hindi | hi_IN | custom-schema-box-generator-hi_IN.po |

Full list: https://make.wordpress.org/polyglots/teams/

## 📝 Translation Tips

### 1. Context Matters
- Read the source reference to understand where the string appears
- Consider the UI context (button, heading, description, etc.)
- Keep translations consistent with WordPress core translations

### 2. Preserve Formatting
- Keep HTML tags: `<strong>`, `<br>`, `<code>`, etc.
- Keep placeholders: `%s`, `%d`, `%1$s`, etc.
- Keep special characters: `\n` (newline), `\t` (tab)
- Keep emojis: ☕, ❤️, 🚀, etc.

### 3. String Length
- Try to keep similar length to original
- UI elements have limited space
- Test your translation in the actual interface

### 4. Technical Terms
- "Schema" - Usually kept as "Schema" in most languages
- "JSON-LD" - Keep as is (technical term)
- "Meta Box" - May be translated or kept as is
- "Post Type" - Translate according to WordPress core translation

### 5. Tone and Style
- Keep professional and friendly tone
- Use formal or informal "you" based on language conventions
- Follow WordPress translation guidelines for your language

## 🔍 Testing Your Translation

1. **Install translation files**
   ```
   wp-content/plugins/custom-schema-box-generator/languages/
   ├── custom-schema-box-generator-es_ES.po
   └── custom-schema-box-generator-es_ES.mo
   ```

2. **Change WordPress language**
   - Go to Settings → General
   - Set "Site Language" to your language
   - Save changes

3. **Check the plugin**
   - Go to Settings → Schema Box Generator
   - Verify all strings are translated
   - Check for layout issues
   - Test all tabs and features

4. **Common issues**
   - Translation not showing? Check file names match locale exactly
   - Partial translation? Regenerate `.mo` file from `.po`
   - Broken layout? Check string length and formatting

## 📤 Contributing Your Translation

### Option 1: GitHub Pull Request
1. Fork the repository
2. Add your translation files to `/languages/`
3. Create a pull request
4. Include screenshots if possible

### Option 2: WordPress.org
1. Translate on WordPress.org translation platform
2. Translations will be included automatically

### Option 3: Email
1. Send `.po` and `.mo` files to plugin author
2. Include your name for credits
3. Mention any special notes

## 🎖️ Translation Credits

Translators will be credited in:
- Plugin readme file
- WordPress.org plugin page
- Plugin settings page (future feature)

## 📚 Resources

### WordPress Translation Resources
- [WordPress Translator Handbook](https://make.wordpress.org/polyglots/handbook/)
- [WordPress Glossary](https://translate.wordpress.org/projects/wp/dev/)
- [Translation Style Guide](https://make.wordpress.org/polyglots/handbook/translating/glossary-style-guide/)

### Tools
- [Poedit](https://poedit.net/) - Translation editor
- [Loco Translate](https://wordpress.org/plugins/loco-translate/) - WordPress plugin for translating
- [GlotPress](https://wordpress.org/plugins/glotpress/) - WordPress translation platform

### Validation
- [POEdit Validator](https://poedit.net/) - Built-in validation
- [msgfmt](https://www.gnu.org/software/gettext/manual/html_node/msgfmt-Invocation.html) - Command-line validator

## ❓ Questions?

If you have questions about translating this plugin:

1. **Check existing translations** - See how others translated similar strings
2. **Ask the community** - WordPress Polyglots Slack channel
3. **Contact plugin author** - Open an issue on GitHub
4. **WordPress forums** - Post in the plugin support forum

## 🙏 Thank You!

Your translation helps make this plugin accessible to more people around the world. Thank you for your contribution!

---

**Plugin:** Custom Schema Box Generator  
**Text Domain:** custom-schema-box-generator  
**Author:** FARAZFRANK  
**Website:** https://wpfrank.com


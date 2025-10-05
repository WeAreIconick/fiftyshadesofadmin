# Fifty Shades of Admin

**Version:** 1.0.1  
**Author:** Iconick  
**License:** GPL v2 or later  
**Text Domain:** fifty-shades-of-admin

## Description

Turn your boring WordPress admin into a colorful masterpiece! Pick a color, we'll do the math. Warning: Side effects may include actually enjoying WordPress admin work.

## Features

- 🎨 **Custom Color Schemes**: Choose any color and automatically generate a complete admin theme
- 🔒 **Security First**: Built with WordPress security best practices
- 🌍 **Internationalization Ready**: Fully translatable with proper text domain
- ⚡ **Performance Optimized**: Lightweight and efficient
- 🛡️ **Error Handling**: Comprehensive error handling and logging
- 🔧 **Clean Code**: Follows WordPress coding standards

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher

## Installation

1. Upload the plugin files to `/wp-content/plugins/fifty-shades-of-admin/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to your profile page to customize your admin colors

## Usage

1. Navigate to **Users → Your Profile** (or **Users → All Users → Edit** for other users)
2. Select "Fifty Shades" from the Admin Color Scheme options
3. Choose your desired color using the color picker
4. Click "Apply Color" to see your new admin theme

## Security Features

This plugin has been thoroughly audited and includes:

- ✅ **SQL Injection Protection**: Uses WordPress database abstraction layer
- ✅ **CSRF Protection**: Proper nonce verification on all forms and AJAX requests
- ✅ **XSS Prevention**: All output is properly escaped
- ✅ **Authorization Checks**: Proper capability checks before any user modifications
- ✅ **Data Sanitization**: All input data is sanitized and validated
- ✅ **Error Handling**: Comprehensive error handling with logging

## Code Quality

- **WordPress Standards**: Follows WordPress coding standards
- **Internationalization**: All strings are translatable
- **Plugin Lifecycle**: Proper activation/deactivation/uninstall hooks
- **Performance**: Optimized queries and conditional asset loading
- **Clean Architecture**: Well-organized, maintainable code

## Translation

The plugin is fully internationalized and ready for translation. Translation files are located in the `/languages/` directory.

To create a translation:
1. Copy `languages/fifty-shades-of-admin.pot` to your language code (e.g., `fifty-shades-of-admin-es_ES.po`)
2. Translate the strings in the .po file
3. Compile to .mo file using tools like Poedit or WP-CLI

## Changelog

### 1.0.1
- 🔒 **Security**: Comprehensive security audit and improvements
- 🌍 **Internationalization**: Added full translation support
- 🧹 **Code Quality**: Removed duplicate code and improved error handling
- 🔧 **Plugin Lifecycle**: Added proper activation/deactivation/uninstall hooks
- 📝 **Documentation**: Added comprehensive documentation

### 1.0.0
- 🎨 Initial release with custom color scheme functionality

## Support

For support, feature requests, or bug reports, please visit [Iconick](https://iconick.io).

## License

This plugin is licensed under the GPL v2 or later.

---

*Made with ❤️ by Iconick*

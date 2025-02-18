# Changelog

## Version 2025021800

### Fixed

- Removed superfluous "name" field from rule instances.
- Add ratelimit to bundled rule plugins list.
- Ensure fieldsets are closed on regstration form before adding new elements.

## Version 2025021300

### Added

- New Rate Limit rule for limiting the number of registration attempts over a period of time based on the user's session and/or IP address.

### Fixed

- Issue #65: Removed unused dB tables.
- Issue #66: Make some CSS rules more specific.

## Version 2025020700

### Added

- New Cloudflare Turnstile CAPTCHA rule.
- Added separate dropdown for CAPTCHA rules.
- Automatic selection of text in forced instances modal.

### Fixed

- Improved notifications and guidance text.
- Only one CAPTCHA rule can be added at a time.
- Fixed some typos and incorrect links.
- Use remote IP for hcaptcha.
- Ensure plugin does no processing if not enabled.

## Version 2025012000

### Added

- Migrate away from using $CFG->tool_registrationrules_forcedinstances setting in config.php, now using $CFG->forced_plugin_settings properly.
- Add more detail to readme.

### Fixed

- Address some stylelint warnings.

## Version 2025011800

### Added

- A message is now displayed on the Registration rule instances page to highlight that there are disabled rule plugins that cannot added.
- The hCaptcha settings page now provides a link to documentation detailing how to find a sitekey and secret.

### Fixed

- Bundled rule plugins will now be enabled by default on install.
- Improved plugininfo code.

### Security

- Added missing admin capability check in editruleinstance.php

## Version 2025011700

### Fixed

- Add better validation for forced instance config json.

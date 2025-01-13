# Moodle Registration Rules

The **Registration Rules** plugin enhances user account registration control in Moodle by incorporating various **anti-spam** measures to prevent automated spam bots from creating fake accounts.

## :rocket: List of features

- Flexible rules to allow or deny account registration around date/time windows.
- Extend signup form to include [hCaptcha](https://www.hcaptcha.com/) or [Altcha](https://github.com/altcha-org).
- Prevent accounts from being created with [disposable email addresses](https://en.wikipedia.org/wiki/Disposable_email_address).
- Check, warn and/or deny signup if password is listed in the [Have I Been Pwned](https://haveibeenpwned.com/) database.
- Check and deny signup if IP address, email address or username are listed in the [Stop Forum Spam](https://www.stopforumspam.com/) database.
- Implement other anti-spam measures such as injecting randomised hidden honeypot fields into the signup form and ensuring users take a minimum amount of time to register.
- Entirely disable user signups with a single click, displaying a custom message on the signup page.

## 🧐 How it works

The **Registration Rules** plugin enables you to configure a set of customisable rules, selected from a list of available rule plugins. Each rule specifies a condition that is evaluated to determine whether a user is permitted to register an account.

When configuring a rule, a score can be assigned that will be added if the condition is met. Once all rules have been evaluated, the total score is compared against the maximum threshold set on the plugin's main settings page. Registration is allowed only if the accumulated score remains below this threshold.

## :pencil: How to use

- [Install the plugin](https://docs.moodle.org/en/Installing_plugins#Installing_a_plugin) in Moodle.

- Browse to "Site administration / Admin tools / Registration rules / Registration rule instances".

- Select and configure the rules you want to use via the "Add rule" dropdown.

- Browse to "Site administration / Admin tools / Registration rules / Registration rules settings" and tick "Enable".

- While on the "Registration rules settings" page you may wish to consider ticking the "Logging only" option initially, the plugin will then evaluate rules as normal but will only log the results instead of denying user registration. 

## Proudly developed in MoodleMoot DACH 2024

Moodle Registration Rules was originally developed during [MoodleMoot DACH](https://moodlemootdach.org) 2024 as a collaboration between Dale Davies from [Catalyst IT Europe](https://www.catalyst-eu.net/), Philipp Hager and Andreas Hruska from [eDaktik](https://www.edaktik.at/), Lukas Müller from [lern.link](https://lern.link/) and Michael Aherne from [University of Strathclyde](https://www.strath.ac.uk/).
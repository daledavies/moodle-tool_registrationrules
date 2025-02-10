# Moodle Registration Rules

The **Registration Rules** plugin enhances user account registration control in Moodle by incorporating various **anti-spam** measures and reCAPTCHA alternatives, to prevent automated spam bots from creating fake accounts.

## :rocket: List of features

- Extend the signup form to include reCAPTCHA alternatives from [ALTCHA](https://github.com/altcha-org), [Cloudflare Turnstile](https://www.cloudflare.com/en-gb/application-services/products/turnstile/) and [hCaptcha](%5Bhttps://www.hcaptcha.com/%5D(https://www.hcaptcha.com/))
- Prevent accounts from being created with [disposable email addresses](https://en.wikipedia.org/wiki/Disposable_email_address).
- Check, warn and/or deny signup if password is listed in the [Have I Been Pwned](https://haveibeenpwned.com/) database.
- Check and deny signup if IP address, email address or username are listed in the [Stop Forum Spam](https://www.stopforumspam.com/) database.
- Implement other anti-spam measures such as injecting randomised hidden honeypot fields into the signup form and ensuring users take a minimum amount of time to register.
- Flexible rules to allow or deny account registration around date/time windows.
- Entirely disable user signups with a single click, displaying a custom message on the signup page.

## 🧐 How it works

The **Registration Rules** plugin lets you set up a series of flexible rules to control user signups based on specific conditions. Each rule defines a check that is run during the registration process.

### How rules are evaluated:

1. When a user tries to sign up, the plugin checks their submitted details against the set rules.
2. Each rule has a score. If the rule's condition is met, the corresponding score is added to the total.
3. After all rules are checked, the plugin compares the total score with a configurable threshold.
4. If the total score is below the threshold then registration is allowed. If it is above the threshold, the registration is denied.

## :pencil: How to use

### Basic usage

- [Install the plugin](https://docs.moodle.org/en/Installing_plugins#Installing_a_plugin) in Moodle.

- Browse to "Site administration / Plugins / Admin tools / Registration rules / Registration rule instances".

- Select and configure the rules you want to use via the "Add rule" and/or "Add CAPTCHA" dropdown.

- Browse to "Site administration / Plugins / Admin tools / Registration rules / Registration rules settings" and tick "Enable".

- While on the "Registration rules settings" page you may wish to consider ticking the "Logging only" option initially, the plugin will then evaluate rules as normal but will only log the results instead of denying user registration.

### Forced instances

When you have found your perfect configuration it is possible to enforce this via `config.php` so that it cannot be changed via the admin interface, this is particularly useful for deploying sites with a preset configuration.

For more information, click the "View instances JSON" button on the "Registration rule instances"" page.

### Bundled CAPTCHA plugins

#### ALTCHA challenge

ALTCHA is a free, open-source reCAPTCHA alternative designed to protect your website from spam and abuse. It respects user privacy by avoiding the use of cookies, fingerprinting, or tracking, and is fully compliant with GDPR regulations.

ALTCHA functions by generating a challenge that the user's web browser must solve when they check the "I'm not a robot" box on a signup form. This rule includes a complexity setting, which determines the level of computational effort required to complete the challenge. Higher complexity enhances security but results in a longer wait time for the challenge to be solved.

- *This plugin does not require a third party user account and does not communicate with external systems.*

#### Cloudflare Turnstile

Cloudflare Turnstile is a privacy-preserving CAPTCHA alternative designed to verify user interactions without compromising user data. Unlike traditional CAPTCHA systems, Turnstile uses a non-intrusive browser-based challenge and behavioural analysis to differentiate between humans and bots, all without requiring users to solve puzzles or identify objects. Compliant with GDPR, it ensures a seamless and user-friendly experience while maintaining robust security.

- *This plugin requires a user account to be created at https://www.cloudflare.com and communicates with external systems.*

#### hCaptcha

hCaptcha is a reCAPTCHA alternative. Unlike some other CAPTCHA solutions, hCaptcha focuses on compliance with privacy laws like GDPR and does not sell user data. It works by presenting users with a visual challenge, such as identifying specific objects in images, to verify they are human.

- *This plugin requires a user account to be created at https://www.hcaptcha.com and communicates with external systems.*

### Bundled rule plugins

#### Disposable emails

This rule ensures that accounts cannot be created using [disposable email addresses](https://en.wikipedia.org/wiki/Disposable_email_address). This means that users must provide a valid, permanent email address to register.

Disposable email addresses are temporary and intended for short-term use. They allow people to quickly obtain an email address without sharing their primary one. However, these anonymous addresses can also be misused by bad actors on the Internet, such as spammers.

#### Have I Been Pwned?!

Check, warn and deny signup if the user's password is listed in the [Have I Been Pwned](https://haveibeenpwned.com/) database, a free online service that aggregates details of data breaches.

- *This plugin communicates with external systems but does not require you to register for a user account.*

#### Hidden honeypot field

A **honeypot field** is a hidden field added to the signup form that is designed to trap and identify bots. It is a simple, effective technique for reducing spam and automated submissions on websites. 

A random field will be added to the signup form that is invisible to the user, if this field contains data when the form is submitted then it must have been completed by a bot.

#### Limit by date/time

Control exactly when your users are allowed to register for new accounts by defining flexible rules to allow or deny account registration around date/time windows.

#### Minimum completion time

This rule helps to prevent spam by measuring how quickly the signup form is filled out and submitted. Since bots can typically complete forms almost instantly, this check helps distinguish between human users and automated account registration.

#### Nope

Completely disable account registration with one click, displaying a message to users in place of the signup form.

#### Rate Limit

Prevent brute-force registration attacks by limiting the number of registration attempts based on the user's session and/or IP address. This rule allows administrators to set individual parameters for both session and IP-based rate limiting, specifying the maximum number of allowed attempts within a defined time window. If the limit is exceeded, further registration attempts are temporarily blocked.

#### Stop Forum Spam

This helps reduce spam account registrations by comparing the submitted username, email address and IP address against a global database of known spammers provided by the free [Stop Forum Spam](https://www.stopforumspam.com/) service, an effective layer of protection against automated and malicious account creation.

- *This plugin communicates with external systems but does not require you to register for a user account.*

---

## Proudly developed in MoodleMoot DACH 2024

Moodle Registration Rules was originally developed during [MoodleMoot DACH](https://moodlemootdach.org) 2024 as a collaboration between Dale Davies from [Catalyst IT Europe](https://www.catalyst-eu.net/), Philipp Hager and Andreas Hruska from [eDaktik](https://www.edaktik.at/), Lukas Müller from [lern.link](https://lern.link/) and Michael Aherne from [University of Strathclyde](https://www.strath.ac.uk/).

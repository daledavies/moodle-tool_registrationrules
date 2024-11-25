# HCaptcha registration rule

## Description

Have I been pwned registration (HIBP) rule plugin uses HIBP API to check user's provided password for existence in
publicly available data dumps.

## Privacy policy addition

It is recommended to be transparent about HIBP usage in the site's privacy policy—even for unauthenticated users.
HIBP plugin only submits the first five characters of the user's hashed password to fetch a range of possibly corrupted
password hashes. Then this list is locally compared to the full password hash to determine if it is included in past
data dumps. No clear-text password, complete password hash, or other user related data is sent to HIBP.

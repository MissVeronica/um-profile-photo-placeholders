# UM Profile Photo Placeholders
Extension to Ultimate Member for six new placeholders creating profile photo links and inline embedded photos with three different sizes in UM notification emails.
## UM Settings -> Emails -> "Email template"
* Enable plugin - Click to enable the "Profile Photo Placeholders" plugin for this email template.
* Enable inline embedded Photos - Click to HTML inline embed the Profile Photo base64 encoded image {profile_photo_embed_x} for this email template.
* Enable circular photos - Click to make the Profile photo circular in this email template.
* Select border width - Select a border width or 0px for no border.
* Enter border color - Enter border color either HTML color name or HEX code. Default color: white [W3SCHOOL HTML Color names](https://www.w3schools.com/tags/ref_colornames.asp)
## Shortcodes
* The shortcode suffixes s, m, l for small, medium, large Profile Photos
* Sizes of the Profile Photos from UM Settings -> General -> Uploads -> "Profile Photo Thumbnail Sizes (px)"
* Less than three Thumbnail sizes for a User Profile Photo a smaller existing photo will be used.
### Links
* {profile_photo_link_s}
* {profile_photo_link_m}
* {profile_photo_link_l}
### Embedded
* {profile_photo_embed_s}
* {profile_photo_embed_m}
* {profile_photo_embed_l}

### Cons of linked images
* Suffers the same blocking problems as Base64 encoding on most services
* Requires download from external servers
### Cons of HTML inline embedding images
* Can really increase the size of emails
* Is most likely blocked by default in many webmail services
* Is blocked completely in Outlook
### How to Embed Images in Your Emails
Twilio guide: https://www.twilio.com/en-us/blog/insights/embedding-images-emails-facts
## Translations or Text changes
* Use the "Say What?" plugin with text domain ultimate-member
* https://wordpress.org/plugins/say-what/

## Updates
None

## Installation & Updates
* Install and update by downloading the plugin ZIP file via the green "Code" button
* Install as a new Plugin, which you upload in WordPress -> Plugins -> Add New -> Upload Plugin.
* Activate the Plugin

# whmcs-hook-zulip

This project is based open the whmcs-hook-slack project (https://github.com/ilhamrizqi/whmcs-hook-slack/) which was used as the inital framework for development.


WHMCS hook to Zulip. Send notification to zulip stream on creation of a new ticket or upon ticket reploy.

## Installation

Just copy `zulip.php` and `zulip.json` to `$WHMCS_ROOT/includes/hooks` directory.

## Configuration

Edit file `zulip.json` and change `hook_url` to your zulip API url and add the username and password for a bot user to post the messages.

```json
  {
    "hook_url"  : "https://YOUR_ZULIP_DOMAIN/api/v1/messages",
    "botuser"   : "YOUR_ZULIP_BOT_USER",
    "api_key"   : "YOUR_ZULIP_BOT_APIKEY",
    "stream"    : "support",
    "adminuser" : "your_whmcs_admin_username"
  }
```
Explanation

* `hook_url`: your zulip API URL
* `botuser`: Zulip bot user who will post the messages
* `apikey`: Zulip API key for the bot user
* `stream`: Zulip stream to receive the notification
* `adminuser`: WHMCS admin username to call WHMCS API admin function

## Done

Now, try open ticket in the client area. You should receive notification from WHMCS every ticket open and user reply.

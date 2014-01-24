Setting up your Link Shortener with Ducktape.php
================================================

Currently Using Bit.ly
------

### How to Use the Link Shortener with Your Site ###

Add the following to your /basedir/local/config.yml

```
linkshortener:
  api: 'bitly'
  username: 'username'
  clientapikey: 'apikey'
```

Once included, an example of how to use the code is in the demo directory of this module.

TODO
----

Eventually the apikey will no longer be able to function due to the fact it is deprecated.  We will work on making this ready for OAuth ASAP!

##### Resources #####

* http://dev.bitly.com
* https://bitly.com/a/create_oauth_app

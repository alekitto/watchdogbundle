## KCS Watchdog bundle for Symfony2

### Requirements:

* Symfony2
* Doctrine 2

### Installation:


* Include this bundle in your composer.json

```javascript
    "require": {
        ...
        "kcs/watchdog-bundle": "dev-master",
        ...
        }
```

* Create the watchdog table on your database
* Enjoy!


If you want to use Doctrine CouchDB ODM you have to add this to your configuration:

```yaml
...

kcs_watchdog:
    db_driver:          orm         # Allowed values "orm" (default), "couchdb"

...
```

By default it will not log exceptions and errors if ```kernel.debug``` flag is
set. If you want to enable the error and exceptions logging in debug mode you
can set the ```log_in_debug``` flag to true.

```yaml

kcs_watchdog:
    log_in_debug: true

```

You can ignore some exceptions you don't want to log; Example:

```yaml

kcs_watchdog:
    allowed_exceptions:
        - Symfony\Component\HttpKernel\Exception\NotFoundHttpException

```

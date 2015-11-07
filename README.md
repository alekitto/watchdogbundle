## KCS Watchdog bundle for Symfony

### Requirements:

Symfony >= 2.8.0

### Installation:

```bash
$ composer require kcs/watchdog-bundle
```

### Configuration

By default this bundle uses doctrine orm to persist the errors to database.
You can override the persister implementing `Storage\StorageInterface` and
specifying the new persister service id into `persister` configuration parameter

```yaml
...

kcs_watchdog:
    persister:          app_my_custom_persister

...
```

You can ignore some exceptions you don't want to log; Example:

```yaml

kcs_watchdog:
    allowed_exceptions:
        - Symfony\Component\HttpKernel\Exception\NotFoundHttpException

```

Setting the `enabled` config parameter to `false` the the bundle will be
completly disabled. Services and parameters will be not loaded into the container

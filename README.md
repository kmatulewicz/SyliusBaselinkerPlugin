<h1 align="center">Sylius Baselinker Plugin</h1>


<p align="center">This plugin is work in progress, do not use this plugin to other purpose then testing, especially do not use this plugin in production environment.</p>

How to test plugin
===================
The fastest method to run dev environment is to use docker:

    docker compose up -d
    docker compose exec app make init

Change `BL_TOKEN` in `tests/Application/.env`.
Now you can set plugin in admin panel on your localhost: `http://localhost/admin`, login is `sylius`, password is the same as login. You can run sync commands:

    docker compose exec app make symfony baselinker:orders:add
    docker compose exec app make symfony baselinker:orders:payments
    docker compose exec app make symfony baselinker:orders:statuses

or tests:

    docker compose exec app make test


Installation
=============
The plugin can be installed in an existing or new sylius-standard application.

1. Run:

    ```bash
    composer require kmatulewicz/sylius-baselinker
    ```

2. Check presence of plugin entry in `config/bundles.php`:

    ```
    SyliusBaselinkerPlugin\SyliusBaselinkerPlugin::class => ['all' => true],
    ```

3. Add to `config/packages/_sylius.yaml`: 
    
    ```yaml
    - { resource: "@SyliusBaselinkerPlugin/Resources/config/config.yml" }
    ```

4. Add to `config/routes.yaml`:

    ```yaml
    sylius_baselinker:
        resource: "@SyliusBaselinkerPlugin/Resources/config/routing.yml"
    ```

5. Add `SyliusBaselinkerPlugin\Entity\OrderInterface` and `SyliusBaselinkerPlugin\Entity\OrderTrait` to `src/Entity/Order/Order.php`. Final result should be similar to:

    ```php
    // src/Entity/Order/Order.php
    // [...]
    use SyliusBaselinkerPlugin\Entity\OrderInterface;
    use SyliusBaselinkerPlugin\Entity\OrderTrait;
    // [...]
    class Order extends BaseOrder implements OrderInterface
    {
        use OrderTrait;
    }
    ```

6. Add `BL_TOKEN` to your .env file. Token can be found in Baselinker > My Account > API. If you do not have a token already, create a new one. Final result should be similar to:

    ```
    BL_TOKEN=token_copied_from_baselinker
    ```

7. Execute migrations:

    ```bash
    bin/console doctrine:migrations:migrate
    ```

8. Rebuild the cache to display all new translations correctly:

    ```bash
    bin/console cache:clear
    bin/console cache:warmup
   ```

9. Go to admin panel and set order source and statuses associations on Baselinker section.

Usage
======

This plugin provides commands:
1. `baselinker:orders:add` Adds not synchronized shop orders to Baselinker.
2. `baselinker:orders:payments` Adds to Baselinker payments done in shop after last synchronization.
3. `baselinker:orders:statuses` Checks for status changes of synchronized orders. Applies shop order status change to corresponding Baselinker status.

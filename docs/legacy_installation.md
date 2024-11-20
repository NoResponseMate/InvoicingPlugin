### Legacy installation (without Symfony Flex)

1. Require plugin with composer:

    ```bash
    composer require sylius/invoicing-plugin
    ```
    
1. Add plugin class to your `AppKernel`:

    ```php
    $bundles = [
        new \Knp\Bundle\SnappyBundle\KnpSnappyBundle(),
        new \Sylius\InvoicingPlugin\SyliusInvoicingPlugin(),
    ];
    ```

1. Import configuration:

    ```yaml
    imports:
        - { resource: '@SyliusInvoicingPlugin/config/config.yaml' }
    ```

1. Import routing:

    ```yaml
    sylius_invoicing_plugin_admin:
        resource: '@SyliusInvoicingPlugin/config/admin_routes.yaml'
        prefix: '/%sylius_admin.path_name%'
    
    sylius_invoicing_plugin_shop:
        resource: '@SyliusInvoicingPlugin/config/shop_routes.yaml'
        prefix: /{_locale}
        requirements:
            _locale: ^[a-z]{2}(?:_[A-Z]{2})?$
    ```

1. Check if you have `wkhtmltopdf` binary. If not, you can download it [here](https://wkhtmltopdf.org/downloads.html).

    In case `wkhtmltopdf` is not located in `/usr/local/bin/wkhtmltopdf`, add a following snippet at the end of your application's `config.yml`:
    
    ```yaml
    knp_snappy:
        pdf:
            enabled: true
            binary: /usr/local/bin/wkhtmltopdf # Change this! :)
            options: []
    ```

1. Apply migrations to your database:

    ```bash
    bin/console doctrine:migrations:migrate
    ```

1. If you want to generate invoices for orders placed before plugin's installation run the following command using your terminal:

   ```bash
   bin/console sylius-invoicing:generate-invoices
   ```

1. Clear cache:

    ```bash
    bin/console cache:clear
    ```

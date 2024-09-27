# Best and most sophisticated currency converter ;-)

## Table of Contents

1. [Project setup](#project-setup)
2. [SSH to container](#ssh-to-container)
3. [Testing](#testing)
    - [First time setup](#first-time-setup)
    - [Run the tests](#run-the-tests)
4. [Manage the application](#manage-the-application)
    - [User Management Commands](#user-management-commands)
        - [Add a User](#add-a-user)
        - [List Users](#list-users)
        - [Remove a User](#remove-a-user)
    - [IP Management Commands](#ip-management-commands)
        - [Add an IP Address](#add-an-ip-address)
        - [List IP Addresses](#list-ip-addresses)
        - [Remove an IP Address](#remove-an-ip-address)
    - [Currency Management Commands](#currency-management-commands)
        - [List Supported Currencies](#list-supported-currencies)
        - [Update Exchange Rates](#update-exchange-rates)

## Project setup

Run the following command to start the project:

```bash
docker compose up
```

NOTE: This command could potentially take a while to finish on initial run as it'll have to install the dependencies, create a database and build the project.

Now you can optionally run the following command to load the fixtures:

```bash
# When on host machine:
docker compose exec php sh -c "php bin/console doctrine:fixtures:load"

# When inside the container:
php bin/console doctrine:fixtures:load
```

BE AWARE: This will load the fixtures in the database, which means that the exchange rates will be set to the values defined in the `CurrencyExchangeRateFixtures`
class as opposed to the real-time exchange rates which have been loaded automatically after the migrations ran. This however can still be updated later (see [Update Exchange Rates](#update-exchange-rates)).

Once the project is running, you can access the following URLs:

- App: https://localhost
- App admin: https://localhost/admin
- Adminer: http://localhost:8081
- Mailpit: http://localhost:8025

If you chose to load the fixtures, you can login with the following credentials:

- Admin: admin@example.com / admin
- User: test@example.com / password

## SSH to container

To get a shell inside the container (eg. to run `bin/console` commands), run the following command:

```bash
docker compose exec php sh
```

## Testing

### First time setup

Before you run any tests, you need to make sure to setup a test database.

To do so, run the following command in your container:

```bash
bin/console --env=test doctrine:database:create
bin/console --env=test doctrine:migrations:migrate
bin/console --env=test doctrine:fixtures:load
```

### Run the tests

To run the tests, run the following command:

```bash
./bin/phpunit.sh
```

## Manage the application

The application can be managed from the admin area, found at https://yourdomain.com/admin. Besides that, you can also fully manage the application using the command line.
This is especially usefull when initially setting up your application as you'll have to create an admin user via the command line before you even have access to the admin panel.

### User Management Commands

#### Add a User

To add a new user, run the following command:

```bash
php bin/console app:access:user:add <email> <password> [--admin]
```

- `<email>`: The email of the user.
- `<password>`: The password of the user.
- `--admin`: (Optional) If provided, the user will be given admin privileges.

Example:

```bash
php bin/console app:access:user:add user@example.com password
php bin/console app:access:user:add admin@example.com admin --admin
```

#### List Users

To list all users, run the following command:

```bash
php bin/console app:access:user:list
```

This will display a table with the ID, email, and roles of each user.

#### Remove a User

To remove a user, run the following command:

```bash
php bin/console app:access:user:remove <email>
```

- `<email>`: The email of the user to remove.

### IP Management Commands

#### Add an IP Address

To add a new IP address to the allowed list, run the following command:

```bash
php bin/console app:access:ip:add <ip>
```

- `<ip>`: The IP address to add.

Example:

```bash
php bin/console app:access:ip:add 192.168.1.100

# or an IP subnet
php bin/console app:access:ip:add 192.168.1.0/24
```

#### List IP Addresses

To list all allowed IP addresses, run the following command:

```bash
php bin/console app:access:ip:list
```

This will display a table with the ID and IP address of each allowed IP.

#### Remove an IP Address

To remove an IP address from the allowed list, run the following command:

```bash
php bin/console app:access:ip:remove <ip>
```

- `<ip>`: The IP address to remove.

### Currency Management Commands

#### List Supported Currencies

To list all currently supported currencies, run the following command:

```bash
php bin/console app:currencies:list
```

This will display a list of all supported currency codes.

#### Update Exchange Rates

To update the exchange rates for the given currencies, run the following command:

```bash
php bin/console app:currencies:update [<currency_codes>] [--all] [--async]
```

- `<currency_codes>`: (Optional) A list of currency codes to update. Separate multiple codes with a space.
- `--all`: (Optional) If provided, updates all existing currencies.
- `--async`: (Optional) If provided, updates currencies asynchronously.

Example:

```bash
php bin/console app:currencies:update USD EUR
php bin/console app:currencies:update --all
php bin/console app:currencies:update USD EUR --async
```
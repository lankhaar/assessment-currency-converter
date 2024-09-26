# Best and most sophisticated currency converter ;-)

## Project setup

Run the following command to start the project:

```bash
docker compose up
```

NOTE: This command could potentially take a while to finish, on initial run as it will install the dependencies, create a database and build the project.

Now you can optionally run the following command to load the fixtures:

```bash
# When on host machine:
docker compose exec php sh -c "php bin/console doctrine:fixtures:load"

# When inside the container:
php bin/console doctrine:fixtures:load
```

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
docker composer exec php sh
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

To run the tests, run the following command:

```bash
./bin/phpunit.sh
```

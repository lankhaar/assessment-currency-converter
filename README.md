# Best and most sophisticated currency converter ;-)

## Project setup

Run the following command to start the project:

```bash
docker compose up
```

NOTE: This command could potentially take a while to finish, on initial run as it will install the dependencies, create a database and build the project.

Once the project is running, you can access the following URLs:

- App: https://localhost
- Adminer: http://localhost:8081
- Mailpit: http://localhost:8025

## SSH to container

To get a shell inside the container (eg. to run `bin/console` commands), run the following command:

```bash
docker composer exec php sh
```

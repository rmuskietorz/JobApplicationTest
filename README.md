# Job application test task

## Project Initialisation

Open the project folder and run

```bash
composer install
```

## Installation

Install ddev from https://ddev.com/, docker and wsl for starting Project and Database.

After installing the desired Programs go into the root folder with a terminal and execute.

```bash
ddev start
```

Now the application will run inside a docker.

## Update Database

To update the Database run

```bash
ddev exec php bin/console doctrine:migrations:migrate
```

## API Instructions

As soon as ddev is running you can set up the API queries using the url in the terminal.

In my test I used Postman to send push, get and delete requests

If no URL will be displayed in the Terminal then run

```bash
ddev describe
```
and use the URL from the "web" service.



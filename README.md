# Collect2Connect Webshop

Collect2Connect is a webshop platform that sells LEGO® parts and minifigures.

## Introduction

The most complex part of building a webshop for LEGO® parts and minifigures is managing the vast inventory of individual pieces. Collect2Connect addresses this challenge by integrating with the Rebrickable API, which provides a comprehensive database of LEGO® parts, sets, and minifigures.

The active articles and stocks are retrieved from Bricqer, a specialized inventory management system for LEGO® sellers. This integration ensures that the webshop always has up-to-date information on available products.

## Installation

Copy the environment variables:

```bash
cp .env.example .env
```

Build docker and start the containers:

```bash
docker-compose up -d
```

Generate an application key:

```bash
docker compose exec laravel php artisan key:generate
```

Run the migrations and seed the database:

```bash
docker compose exec laravel php artisan migrate --seed
```

**Run Vite:**

```bash
docker compose exec laravel npm install
docker compose exec laravel npm run dev
```

## Simple Setup Utility (SSU command)

To make the development easier, use the ```ssu``` command to run artisan, composer, npm and other commands.

### Create a alias for the ssu command (optional)

```bash
alias ssu='./docker/ssu.sh'
```

### Usage

For example, run an artisan command:

```bash
ssu artisan migrate
```

# Unit Test

```shell
PGPASSWORD='33L!0213LYM26.' \
docker compose -f docker-compose.yml -f docker-compose.dev.yml exec -T postgres \
  sh -lc "pg_dump -U 'app_us3r_26\!.' -d 'app_db' --no-owner --no-privileges" \
  > shared-resources/src/TestCase/sqldumps/billifty.pgsql \
  2> shared-resources/src/TestCase/sqldumps/billifty.dump.err
```

## and run the test on specific class

```shell
./vendor/bin/phpunit --filter=UserSubscriptionTest
```

Or

```shell
docker compose -f docker-compose.yml -f docker-compose.dev.yml exec backend php artisan testdb:snapshot
```
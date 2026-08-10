# Reproducing bugs locally

Shared setup for the "How to reproduce" section in the bug issues. Everything below assumes the
Docker stack from the README.

## Start the stack

```bash
docker-compose up -d --build
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
```

- API — `http://localhost`
- phpMyAdmin — `http://localhost:8080`

## Getting a token

Sign-up and login are the same endpoint, and an existing user cannot request a second OTP
(see [#35](https://github.com/themiladmirza/akbarfood/issues/35)). So there are two cases.

**A brand-new phone number** — the real flow works:

```bash
curl -s -X POST http://localhost/api/auth/request-otp \
  -H 'Accept: application/json' -d 'name=فاطمه' -d 'phone=09121112233'
# read the 4-digit code from the Telegram sandbox group, then:
curl -s -X POST http://localhost/api/auth/verify-otp \
  -H 'Accept: application/json' -d 'phone=09121112233' -d 'code=1234'
```

**An existing user (including the seeded admin)** — impossible over HTTP today. Mint a token
directly until #35 is fixed:

```bash
docker-compose exec app php artisan tinker --execute="
  echo auth('api')->login(App\Models\User::where('phone','09002827287')->first());
"
```

Then keep it handy:

```bash
export TOKEN='eyJ0eXAi...'
export AUTH="Authorization: Bearer $TOKEN"
export JSON='Accept: application/json'
```

## Finding ids

Most `POST` endpoints return only a message and never the id of what they created
(see [#56](https://github.com/themiladmirza/akbarfood/issues/56)). Until that is fixed, read ids
straight from the database. `tinker` is the least fiddly way:

```bash
docker-compose exec app php artisan tinker --execute="dump(App\Models\Order\Order::latest('id')->first()?->id);"
docker-compose exec app php artisan tinker --execute="dump(App\Models\Payment\Payment::latest('id')->first()?->id);"
docker-compose exec app php artisan tinker --execute="dump(App\Models\Menu\Menu::latest('id')->first()?->only(['id','name','price']));"
```

phpMyAdmin at `localhost:8080` works too if you prefer clicking.

## A customer with something in their cart

Several reproductions need this state. Shortest path:

1. Register a restaurant (`POST /api/restaurant-register`, multipart, as any logged-in user).
2. Approve it as an operator (`PATCH /api/restaurants/{restaurant}/approve`) — menu endpoints
   reject anything not `approved`.
3. Add a menu item (`POST /api/restaurants/{restaurant}/add_menu_item`, multipart).
4. Add it to the cart:

```bash
curl -s -X POST "http://localhost/api/restaurants/$RESTAURANT/add_item_to_cart" \
  -H "$AUTH" -H "$JSON" -d "menu_id=$MENU" -d 'quantity=1'
```

Promoting the first operator needs an admin token — see "Getting a token" above, and
[#28](https://github.com/themiladmirza/akbarfood/issues/28) for why the admin may not exist yet.

## Note

Output shown in the issues is abbreviated. `tinker --execute` prints some extra framing around the
value; only the value matters.

# Akbarfood — Architecture Review

A living checklist. Findings are numbered so we can refer to them in conversation
("let's drop C4", "B2 first"). Nothing here is final — add, remove, or re-prioritise freely.

**The 30 P0 and P1 findings are filed as GitHub issues** and linked inline below, grouped into four
milestones matching §5. P2 and P3 findings are tracked here only.

Reviewed at commit `b8f64ba`. Every endpoint in `routes/api.php` was walked end to end.

---

## 1. The target: onion architecture

Three rings. **Dependencies point inward only.** An inner ring may never name a class from an
outer ring.

| Ring | Contains | May import |
|---|---|---|
| **Domain** (center) | Enums (`OrderStatus`, `RestaurantStatus`, `Role`, `MenuCategory`), value objects (`Money`, `PhoneNumber`), domain exceptions, invariants and state machines | Eloquent (conceded, see below) — nothing else |
| **Application** | Use-case services (`CreateOrderService`), DTOs (the commands), **ports** (`NotificationService`, and a future `PaymentGateway`) | Domain |
| **Infrastructure / Delivery** (outer) | Controllers, FormRequests, middleware, API Resources, routes, `TelegramNotificationService`, file storage, migrations | Application, Domain |

### The Active Record concession — exactly what it covers

We accept that Eloquent is an Active Record implementation and that this costs us some purity.
Concretely:

**Accepted.** Eloquent models act as entities. Services query them directly. **No repository
interfaces are required**, and `Model::find()`, `->where()`, `->create()` inside a service is fine.

**Not accepted as a knock-on effect.** The concession is about *persistence* only. It does not
license the outer ring to leak inward. These are still bugs:

- `auth()` / `Auth::` / `request()` inside a service — that is HTTP, not persistence.
- `Illuminate\Http\UploadedFile` inside a DTO — that is HTTP.
- `Storage::` inside a service — that is infrastructure; it belongs behind a port.
- A domain exception that builds a `JsonResponse` — that is delivery.
- A business rule enforced only in middleware, a FormRequest, or a column default.

**The test for any rule:** *would this still have to be true if the same action came from a
console command, a queued job, or a test?* If yes, but it is only enforced in an HTTP-specific or
DB-specific place, every other caller silently skips it. That is the bug.

### The second goal: a rich domain model, not an anemic one

Accepting Eloquent as the entity is only worth it if the entity carries **behaviour**. Today every
public method on every model is a relationship — the models are data bags, and every rule about
their state is written as a raw comparison somewhere else. That is the worst of both worlds: Active
Record's coupling with none of its payoff.

So the second standard, alongside the dependency rule:

> **An entity owns the rules about its own state.** A service orchestrates — it loads entities, asks
> them questions, tells them to change, and saves. It does not reach inside and judge.

**The smell test:** *a private service method whose only parameter is an entity is a method on that
entity.* `VerifyPhoneNumberService` has three of them today (`isVerificationCodeWrong(…, Otp $otp)`,
`shouldLimitAttempts(Otp $otp)`, `limitPhoneNumber(Otp $otp)`) — each reads or writes nothing but
that OTP's state.

**The limit — do not build a God model.** Rich means *behaviour about its own state*, not everything:

| Belongs on the entity | Stays in the service |
|---|---|
| `$order->canBeCancelled()` | loading the order |
| `$cart->total()` | opening the transaction |
| `$otp->matches($code)` | sending the notification |
| `$restaurant->isApproved()` | `->save()` / persistence |
| in-memory state changes | queries across other tables |

No queries inside entity methods, no side effects, no accessors or `booted()` hooks holding rules —
a reader must be able to find every rule by opening the entity, not by tracing a lifecycle hook.

---

## 2. Settled — do not re-open

- **Telegram-group OTP delivery is intentional dev scaffolding.** A real Iranian SMS gateway
  replaces it later. *Adjacent* concerns are still in scope (missing env var, sync-vs-queue, rate
  limiting) and appear below.
- **No defensive `->nullable()`.** If a missing value means a bug, `NOT NULL` is right. Prefer
  `->default(...)` when forgiveness is wanted.
- **Formatting is CI's job.** Pint runs on every PR. Nothing below is about spacing, imports, or
  quote style.

---

## 3. Findings

Severity: **P0** broken or exploitable · **P1** core architecture lesson · **P2** should fix ·
**P3** polish.

### A. Layering — the dependency rule

- [ ] **A1 · P1 ([#44](https://github.com/themiladmirza/akbarfood/issues/44)) — Services reach into the HTTP session.**
  `AddItemToCartService.php:20`, `UpdateCartItemService.php:14`, `RemoveCartItemService.php:13` call
  `auth()->id()` / `Auth::user()` directly. Meanwhile `CreateOrderService`, `CancelOrderService` and
  both payment services take `userId` through the DTO. The same decision is made two incompatible
  ways in one layer. The DTO version is correct: those three services cannot currently be called
  from a job, a command, or a test without faking an HTTP session.
  *Fix:* add `userId` to `AddItemToCart` / `UpdateCartItem`, create a `RemoveCartItem` DTO, and read
  `auth()->id()` in the controller only.

- [ ] **A2 · P1 ([#45](https://github.com/themiladmirza/akbarfood/issues/45)) — DTOs carry an HTTP type.**
  `AddMenuItem.php` and `UpdateMenuItem.php` hold `Illuminate\Http\UploadedFile`. The Application
  ring now depends on the delivery ring. It also means the service cannot be exercised without
  constructing a fake upload.
  *Fix:* the controller stores the file and puts a `string $imagePath` in the DTO — or, better, the
  DTO carries raw bytes + filename and a `FileStorage` port does the writing.

- [ ] **A3 · P1 ([#46](https://github.com/themiladmirza/akbarfood/issues/46)) — DTOs carry hydrated Eloquent models.**
  `CancelOrder`, `RequestPayment`, `VerifyPayment` take `Order` / `Payment` objects. A DTO is a
  *command* — it should say what was asked for (`orderId`), not hand over a loaded persistence
  object. As written the service delegates "does this row exist?" to HTTP route-model binding, so a
  non-HTTP caller gets no such check.
  *Fix:* `public int $orderId`, and let the service load it.

- [ ] **A4 · P1 ([#37](https://github.com/themiladmirza/akbarfood/issues/37)) — Domain exceptions build HTTP responses.** *(discuss — this is a real trade-off)*
  All 15 classes in `app/Exceptions/` define `render(): JsonResponse`. This is idiomatic Laravel and
  genuinely tidy — services throw domain language and never touch HTTP. But strictly it inverts the
  dependency: the innermost ring names `JsonResponse` and hardcodes status codes.
  *Two honest options:* (a) keep it and write the exception ring off as a deliberate exemption, or
  (b) make exceptions pure and map them to statuses in one place in `bootstrap/app.php`. Worth
  deciding on purpose rather than by default.

  ⚠️ **Decide this before the transition step of A15.** Today only services throw these, so the
  HTTP-aware exception sits in the Application ring. Once entities enforce their own preconditions,
  the innermost ring starts importing `Illuminate\Http` — the same compromise, one ring deeper.

- [ ] **A5 · P1 ([#48](https://github.com/themiladmirza/akbarfood/issues/48)) — Authorization lives in the delivery ring.**
  `AdminMiddleware`, `OperatorMiddleware`, `RestaurantOwnerMiddleware` encode who may do what. Apply
  the test: promoting an operator from a console command would bypass the admin check entirely.
  Ownership checks are *also* duplicated as `if` statements in `CancelOrderService`,
  `RequestPaymentService`, `VerifyPaymentService` — three copies of one rule — and as a `where()` in
  `ListOrderService`.
  *Fix:* the rule belongs in the service (or a Policy the service calls). Middleware may stay as a
  cheap early rejection, but must not be the only place the rule exists.

- [ ] **A6 · P1 ([#47](https://github.com/themiladmirza/akbarfood/issues/47)) — Controller does infrastructure work.**
  `RestaurantController.php:15` runs `$request->file('permit_scan')->store('permits', 'public')`
  before building the DTO. `RegisterRestaurantService` then compensates for it with
  `DB::afterRollBack(...)`. One responsibility split across two rings — and if the service throws
  before its transaction opens, the file is orphaned.

- [ ] **A7 · P2 — Presentation shaping inside a service.**
  `ListCartItemService.php:19-30` builds the response array. Every other read returns raw Eloquent.
  Formatting is a delivery concern; it sank down because there is nowhere above to put it (see D1).

- [ ] **A8 · P1 ([#49](https://github.com/themiladmirza/akbarfood/issues/49)) — The payment gateway has no port.**
  `NotificationService` is a proper port with a Telegram adapter bound in `AppServiceProvider` —
  exactly right. But `VerifyPaymentService.php:27` fakes a gateway inline with
  `random_int(50000, 100000)`. The same idea, applied in one place and not the other.
  *Fix:* a `PaymentGateway` interface with a `FakePaymentGateway` bound for now. Swapping in Zarinpal
  later becomes a one-line binding change.

- [ ] **A9 · P1 ([#42](https://github.com/themiladmirza/akbarfood/issues/42)) — The DTO is not a self-contained command.** ⭐ *the core one*

  A DTO should be **the whole command**: everything needed to carry out the action, and nothing
  outside it. `UpdateMenuItem` should mean "update *this* item, in *this* restaurant, to *these*
  values, on behalf of *this* user." Instead it carries only the form fields, and the target's
  identity travels beside it as loose positional arguments:

  ```php
  // app/Http/Controllers/Menu/MenuController.php:54
  $updateMenuItemService->execute($updateMenuItem, $restaurant, $menuItem);
  //                              └── values ──┘  └── identity, outside the command ──┘
  ```

  The command has been split in two. `UpdateMenuItem` cannot answer "what was asked for?" on its own,
  so it is not a command — it is a bag of validated inputs.

  **The test:** *could you `json_encode()` this DTO, put it on a queue, and have the service do the
  right thing from that alone?* If not, the command is incomplete.

  Full audit of all 18 use-case services:

  | Service | `execute()` signature | Verdict |
  |---|---|---|
  | `ApproveRestaurantService` | `(ApproveRestaurant)` | ✅ complete |
  | `CreateOrderService` | `(CreateOrder)` | ✅ complete |
  | `ListOrderService` | `(ListOrder)` | ✅ complete |
  | `PromoteToOperatorService` | `(PromoteToOperator)` | ✅ complete |
  | `RejectRestaurantService` | `(RejectRestaurant)` | ✅ complete |
  | `RequestPhoneNumberVerificationService` | `(RequestPhoneNumberVerification)` | ✅ complete |
  | `VerifyPhoneNumberService` | `(VerifyPhoneNumber)` | ✅ complete |
  | `CancelOrderService` | `(CancelOrder)` | ④ DTO holds an `Order` model |
  | `RequestPaymentService` | `(RequestPayment)` | ④ DTO holds an `Order` model |
  | `VerifyPaymentService` | `(VerifyPayment)` | ④ DTO holds a `Payment` model |
  | `UpdateMenuItemService` | `(UpdateMenuItem, Restaurant, Menu)` | ① split — 2 loose models |
  | `AddMenuItemService` | `(AddMenuItem, Restaurant)` | ① split |
  | `RegisterRestaurantService` | `(RegisterRestaurant, int $ownerId)` | ① split |
  | `UpdateCartItemService` | `(UpdateCartItem, CartItem)` | ① split **+** ② hidden `auth()` |
  | `AddItemToCartService` | `(AddItemToCart)` | ② hidden `auth()`; `restaurantId` never arrives |
  | `RemoveCartItemService` | `(CartItem)` | ③ no DTO **+** ② hidden `auth()` |
  | `RemoveMenuItemService` | `(Menu, Restaurant)` | ③ no DTO — two raw models |
  | `ListCartItemService` | `(int $userId)` | ③ no DTO — bare scalar |

  **7 of 18 are clean.** Four distinct failure modes:

  - **① Split command** — identity passed beside the DTO. The DTO describes the *values* but not the
    *target*. (4 services)
  - **② Hidden input** — the service reads `auth()` internally, so a required input appears in
    neither the DTO nor the signature. Invisible at the call site. (3 services, see **A1**)
  - **③ No DTO at all** — a raw Eloquent model or scalar. (3 services)
  - **④ DTO holds a hydrated model** — shape-complete, but carries *state* instead of *identity*, so
    it is not serializable and existence checking is delegated to HTTP route-model binding.
    (3 services, see **A3**)

  *Fix, using the one you pointed at:*
  ```php
  final readonly class UpdateMenuItem {
      public function __construct(
          public int $restaurantId,   // ← was a loose arg
          public int $menuItemId,     // ← was a loose arg
          public int $actorId,        // ← was missing entirely (see A14)
          public string $name,
          public string $description,
          public string $category,
          public ?string $imagePath,  // ← was an UploadedFile (see A2)
          public bool $isAvailable,
          public int $price,
      ) {}
  }

  $updateMenuItemService->execute($updateMenuItem);   // one argument, always
  ```

- [ ] **A14 · P1 ([#43](https://github.com/themiladmirza/akbarfood/issues/43)) — The commands missing an actor are exactly the ones whose authorization leaked
  into middleware.**

  This is the consequence of A9, and the correlation is perfect:

  | DTO carries the actor? | Services | Where the ownership rule lives |
  |---|---|---|
  | **Yes** — `userId` | `CancelOrder`, `RequestPayment`, `VerifyPayment` | ✅ inside the service |
  | **No** | `ApproveRestaurant`, `RejectRestaurant`, `PromoteToOperator`, `AddMenuItem`, `UpdateMenuItem`, `RemoveMenuItem` | ❌ only in middleware |

  `PromoteToOperatorService` receives the id of the user being promoted and nothing else. It is
  therefore **structurally incapable** of checking "is the caller an admin?" — the information never
  reaches it. The rule had nowhere to live but `AdminMiddleware`, and that is why a console command
  or a queued job bypasses it entirely (**A5**).

  So the incomplete command is not a tidiness problem. It is what *forced* the business rule into the
  wrong ring. Add `actorId` to those six DTOs and the authorization check can finally move inward.

- [ ] **A15 · P1 ([#38](https://github.com/themiladmirza/akbarfood/issues/38)) — The models are anemic; state rules live in services as raw comparisons.**

  Every public method on `Restaurant`, `Order` and `Payment` is a relationship. There is **not one
  domain method** on any model. The entities are pure data, so services must reach in and judge the
  state themselves — 8 times across 7 services:

  ```php
  UpdateMenuItemService.php:20   if ($restaurant->status !== 'approved')   // ← and Add:16, Remove:17
  ApproveRestaurantService.php:16 if ($restaurant->status !== 'pending')   // ← and Reject:18
  CancelOrderService.php:17      if ($order->status == 'paid')            // ← and :20 cancelled
  RequestPaymentService.php:17   if ($order->status !== 'pending')
  VerifyPaymentService.php:18    if ($payment->status == 'failed')        // ← and :21 paid
  ```

  This matters more than it looks **because of the Active Record concession**. We accepted Eloquent
  as the entity, which means we took Active Record's coupling cost. The only thing that buys back is
  a rich entity that carries its own behaviour. Data-plus-relationships gets us the cost and none of
  the benefit.

  *Fix — put the predicate on the entity:*
  ```php
  // app/Models/Restaurant/Restaurant.php
  public function isApproved(): bool { return $this->status === RestaurantStatus::Approved; }
  public function isPending(): bool  { return $this->status === RestaurantStatus::Pending; }
  ```
  Pairs directly with **A10**: the entity becomes the single place the enum is consumed, so no
  service ever touches the literal again. Keep the methods pure — no queries, no side effects, not
  accessors or scopes.

  **Name the rule, not the state, where the two can diverge.** For `Restaurant` the three menu
  services all mean one thing, so `isApproved()` is enough today. For `Order` they do not:
  `canBeCancelled()` and `canBePaid()` say what the caller actually needs, and survive a new status
  being added. `isPaid()` callers would all have to change; `canBeCancelled()` callers would not.

  **Applied to `Order`, this closes B2.** `VerifyPaymentService` never asks about the order at all —
  adding `$order->canBePaid()` makes that omission visible at the call site instead of invisible.

  *The ladder:* anemic (today) → predicate on the entity → transition methods that own the change
  (`$order->markPaid()` throwing internally). A predicate still lets a caller forget to ask; a
  transition method makes the illegal move impossible. Do predicates first, transitions when the
  enums land.

  **Entity-by-entity plan.** Everything in the "move" column exists today as a comparison, a loop, or
  a private helper somewhere in a service or middleware:

  | Entity | Behaviour to move onto it | Currently lives in |
  |---|---|---|
  | `Otp` | `matches($code)`, `isExpired()`, `isBlocked()`, `canBeRequestedAgain()`, `registerFailedAttempt()`, `block()` | `VerifyPhoneNumberService` private helpers + `RequestPhoneNumberVerificationService:26-31` |
  | `Cart` | `isEmpty()`, `total()`, `addItem(Menu, int)`, `clear()` | `CreateOrderService:20-27,42` and `ListCartItemService:19-30` — **two different total calculations** |
  | `CartItem` | `lineTotal()`, `increaseQuantity(int)` | `AddItemToCartService:22-23`, both total loops |
  | `Order` | `canBeCancelled()`, `canBePaid()`, `cancel()`, `markPaid()` | `CancelOrderService:17,20`, `RequestPaymentService:17` — and **missing entirely** in `VerifyPaymentService` (B2) |
  | `Payment` | `isPaid()`, `hasFailed()`, `markPaid($transactionId)` | `VerifyPaymentService:18,21,25-29` |
  | `Restaurant` | `isApproved()`, `isPending()`, `approve()`, `reject()` | 5 services |
  | `Menu` | `isAvailable()`, `belongsToRestaurant($r)` | `AddItemToCartService:18`, `UpdateMenuItemService:17`, `RemoveMenuItemService:14` |
  | `User` | `isAdmin()`, `isOperator()`, `owns(Restaurant)` | all three middleware classes (A5) |

  **Two P0s dissolve on the way.**
  - `Cart::total()` **is the fix for B3.** That bug exists precisely *because* the total is computed
    in two services instead of once on the entity — `ListCartItemService` sums `$item->price`,
    `CreateOrderService` sums `$cartItem->menu->price`. One method, one answer, and the two cannot
    disagree again.
  - `Order::canBePaid()` **is the fix for B2**, as above.

  This is the strongest argument for the change: a rich model does not just tidy the code, it makes
  the duplicated-and-divergent rule *impossible to write*.

  **Worked example — the transition step, and the three decisions it forces.**

  Today the write is an anonymous field update with the preconditions stranded above it:
  ```php
  // CancelOrderService.php:15-25
  if ($cancelOrder->order->user_id !== $cancelOrder->userId) throw new AuthorizationException;
  if ($cancelOrder->order->status == 'paid')      throw new OrderAlreadyPaidException;
  if ($cancelOrder->order->status == 'cancelled') throw new OrderAlreadyCancelledException;
  $cancelOrder->order->update(['status' => 'cancelled']);
  ```
  `update(['status' => …])` says *what field to write*. `cancel()` says *what happened*. Only the
  second can carry rules.

  1. **Mutate in memory; let the service persist.** `update()` writes to the DB, so an entity method
     calling it would put persistence in the Domain ring — against the limit set in §1, and it makes
     the entity untestable without a database. Assign, do not save:
     ```php
     // app/Models/Order/Order.php
     public function cancel(): void
     {
         if ($this->status === OrderStatus::Paid)      throw new OrderAlreadyPaidException;
         if ($this->status === OrderStatus::Cancelled) throw new OrderAlreadyCancelledException;
         $this->status = OrderStatus::Cancelled;
     }
     ```
     ```php
     // CancelOrderService — orchestrates, does not judge
     $order = Order::findOrFail($cancelOrder->orderId);
     if (! $order->isOwnedBy($cancelOrder->actorId)) throw new AuthorizationException;
     $order->cancel();
     $order->save();
     ```

  2. **State preconditions go in; authorization stays out.** "Is this order still cancellable?" is
     about the order's own state — it belongs in `cancel()`. "May *this user* cancel it?" is about
     the actor, and the order should not need to know who is asking. Keep it in the service as
     `$order->isOwnedBy($actorId)` (the predicate from the table above), or a Policy the service
     calls. Two different kinds of rule; do not merge them into `cancel(User $actor)`.

  3. **This raises the stakes on A4.** `OrderAlreadyPaidException` renders a `JsonResponse`. While
     only services threw it, an HTTP-aware exception sat in the Application ring — untidy but
     survivable. Once entities throw it, the *innermost* ring imports `Illuminate\Http`. **Decide A4
     before doing this step**, not after.

  Same shape for `Order::markPaid($transactionId)`, `Payment::markPaid(…)`, `Restaurant::approve()`,
  `Restaurant::reject()`, `Otp::registerFailedAttempt()`.

- [ ] **A10 · P1 ([#39](https://github.com/themiladmirza/akbarfood/issues/39)) — The domain vocabulary only exists in migrations.**
  `'pending'`, `'paid'`, `'cancelled'`, `'approved'`, `'owner'`, `'operator'`, `Iranian_food`,
  `juice_and_ice_cream` are raw strings in `enum()` columns, repeated in `in:` validation rules, and
  compared with a mix of `==` and `!==` across services. A reader has to open a migration to learn
  how the business works, and a typo produces a branch that is silently never true.
  *Fix:* PHP 8.1 backed enums in the Domain ring + Eloquent `casts()`.

- [ ] **A11 · P3 — `RegisterRestaurant` breaks DTO uniformity.**
  It is a plain `class` with `public readonly` properties; the other 13 are `final readonly class`.

- [ ] **A12 · P2 — Money is a bare int in three different widths.**
  `menus.price` and `cart_items.price` are `unsignedInteger`; `orders.total_price` and
  `payments.amount` are `unsignedBigInteger`. No `Money` value object, so rounding, currency and
  overflow rules live nowhere. Feeds directly into B3.

- [ ] **A13 · P2 — `promoteOperator` skips the validation layer entirely.**
  `OperatorController.php:13` has no FormRequest, an unused `Request $request` parameter, and an
  untyped `$userId` taken straight from the route. It is the only endpoint with no shape validation.

### B. Correctness — found by walking the endpoints

- [ ] **B1 · P0 ([#35](https://github.com/themiladmirza/akbarfood/issues/35)) — There is no way to log in.**
  `RequestPhoneNumberVerificationService.php:22` throws `PhoneAlreadyRegisteredException` whenever
  the phone already has a user. Signup and login are the same endpoint, so **once a user exists they
  can never obtain another token.** The seeded admin can never authenticate at all — the only way to
  get an admin token today is `auth('api')->login(...)` in tinker.
  *Fix:* the endpoint should send an OTP in both cases and `VerifyPhoneNumberService` should
  `firstOrCreate` the user. Also fixes C3.

- [ ] **B2 · P0 ([#40](https://github.com/themiladmirza/akbarfood/issues/40)) — A cancelled order can still be paid.**
  `VerifyPaymentService.php:18-21` inspects only the *payment's* status, never the order's.
  ```
  POST /api/{order}/payment       → payment pending
  PUT  /api/{order}/cancel_order  → order.status = cancelled   (payment untouched)
  POST /api/{payment}/verify_payment → order.status = paid     ← cancelled order is now paid
  ```
  `RequestPaymentService` correctly blocks creating a *new* payment on a non-pending order, but the
  already-issued one sails through. `CancelOrderService.php:24` never touches the payment row.
  *Root cause, not a missing `if`:* `orders.status` and `payments.status` are two independent columns
  mutated by two services with no invariant tying them together. This wants an order state machine
  in the Domain ring that owns every transition.

- [ ] **B3 · P0 ([#41](https://github.com/themiladmirza/akbarfood/issues/41)) — The cart total and the amount charged can differ.**
  Three places, two answers:
  - `AddItemToCartService.php:29` snapshots the menu price into `cart_items.price`
  - `ListCartItemService.php:22,24` shows the customer that **snapshot**
  - `CreateOrderService.php:24,36` computes the order from `$cartItem->menu->price` — the **live**
    price, ignoring the snapshot

  If the restaurant edits a price between add-to-cart and checkout, the customer is charged an amount
  they were never shown. `cart_items.price` is written and then never read for anything that matters.
  *Fix:* decide which is authoritative, use it everywhere, delete the other.

- [ ] **B4 · P0 ([#28](https://github.com/themiladmirza/akbarfood/issues/28)) — `php artisan db:seed` crashes, and never creates the admin.**
  `DatabaseSeeder` calls the stock `UserFactory`, which writes `email`, `email_verified_at`,
  `password`, `remember_token` — none of which exist on `users` — and omits `phone`, which is
  `NOT NULL UNIQUE`. It also seeds `'email' => 'test@example.com'`. Separately, `DatabaseSeeder`
  never calls `AdminSeeder`, so `migrate --seed` produces no admin even if it did run.
  *Fix:* rewrite `UserFactory::definition()` for the real schema (`phone`, `name`, `role`) and add
  `$this->call(AdminSeeder::class)`.

- [ ] **B5 · P1 ([#30](https://github.com/themiladmirza/akbarfood/issues/30)) — `config/auth.php` points at a class that does not exist.**
  Line 3 imports `App\Models\Restaurant`; the real class is `App\Models\Restaurant\Restaurant`. The
  `providers.restaurants` block is dead config referencing a missing class — it only fails to crash
  because nothing resolves that provider. Delete it or fix the import.

- [ ] **B6 · P0 ([#29](https://github.com/themiladmirza/akbarfood/issues/29)) — A fresh clone 500s on the very first endpoint.**
  `config/services.php:39` reads `env('CHAT_ID')`, but `.env.example` only defines
  `TELEGRAM_BOT_TOKEN`. So `chat_id` is `null`, Telegram rejects the call, `->throw()` fires, and
  `POST /api/auth/request-otp` returns 500. Add `TELEGRAM_CHAT_ID=` to `.env.example` and rename the
  key — `CHAT_ID` is too generic for a shared namespace.

- [ ] **B7 · P1 ([#31](https://github.com/themiladmirza/akbarfood/issues/31)) — Uploaded files are unreachable.**
  Nothing runs `php artisan storage:link`: not `composer setup`, not the README, and `public/storage`
  is gitignored. Every menu image and permit URL 404s.

- [ ] **B8 · P1 ([#57](https://github.com/themiladmirza/akbarfood/issues/57)) — An order has no restaurant.**
  `orders` has no `restaurant_id`, and the cart is global per user rather than per restaurant. So a
  customer can put items from two restaurants into one cart and check out as a single order — and
  **no restaurant can ever list its own orders.** For a food-delivery domain this is a structural
  gap, not a detail.

- [ ] **B9 · P1 ([#32](https://github.com/themiladmirza/akbarfood/issues/32)) — A user can end up with two carts.**
  `carts` has no unique index on `user_id`, and `AddItemToCartService.php:20` calls `firstOrCreate`
  *before* taking the row lock. Two concurrent adds create two carts; afterwards `->first()` and
  `->firstOrFail()` pick one arbitrarily and the items scatter between them.
  *Fix:* `$table->unique('user_id')` on `carts`.

- [ ] **B10 · P2 — The same race duplicates cart items.**
  No `unique(cart_id, menu_id)` on `cart_items`. The `lockForUpdate()` at line 21 only locks rows
  that already exist, so two simultaneous first-adds of the same menu item both insert.

- [ ] **B11 · P2 — Double checkout.**
  `CreateOrderService` never locks the cart, so two concurrent `create_order` calls read the same
  items and produce two orders before either clears the cart.

- [ ] **B12 · P1 ([#56](https://github.com/themiladmirza/akbarfood/issues/56)) — Endpoints never return the id of what they created.**
  `restaurant-register`, `add_menu_item`, `create_order` and `POST /{order}/payment` all return only
  a Persian message. `GET /api/cart` returns name/price/quantity but not `cartItem.id`. Practical
  consequence: **`verify_payment` cannot be reached from the API at all** — you must read
  `payments.id` out of phpMyAdmin. Same for updating or deleting a cart item.

- [ ] **B13 · P2 — Three raw `\DomainException`s bypass the exception pattern.**
  `ApproveRestaurantService.php:17`, `RejectRestaurantService.php:19`,
  `VerifyPhoneNumberService.php:66`. They render as 500s instead of a domain status code, and they
  carry an inline Persian string instead of living in `app/Exceptions/`. Everything else in the
  codebase gets this right.

- [ ] **B14 · P2 — N+1 on the cart.**
  `ListCartItemService.php:21` iterates `$cart->items` and reads `$item->menu->name` with no eager
  load — one query per line item. `ListOrderService` does it correctly with `with('items')`; copy
  that.

- [ ] **B15 · P3 — Wrong noun in a shared error message.**
  `AuthorizationException` says «این سفارش متعلق به شما نیست.» but it is also thrown for payments.

- [ ] **B16 · P2 — Availability is never rechecked at checkout.**
  `is_available` is checked on add-to-cart only. An item can be marked unavailable and still be
  ordered minutes later.

- [ ] **B17 · P2 — `UpdateCartItemService` leaves `price` stale.**
  Changing quantity does not refresh the snapshot, so the cart ages further out of sync (feeds B3).

- [ ] **B18 · P2 — Rejection destroys the record.**
  `RejectRestaurantService.php:23` deletes the row instead of setting `status = 'rejected'`. The
  enum value is therefore unreachable, rejections leave no audit trail, the owner cannot be told
  *why*, and the stored `permit_scan` file is never cleaned up.

- [ ] **B19 · P3 — Redundant re-fetch.**
  `RestaurantApprovalController` route-model-binds `Restaurant $restaurant`, then passes only
  `$restaurant->id`, and `ApproveRestaurantService.php:15` loads it again with `findOrFail`. Two
  queries for one row — a symptom of the id-vs-model inconsistency in A3.

- [ ] **B20 · P3 — Transaction ids can collide silently.**
  `random_int(50000, 100000)` over a 50k space, with no unique index on `payments.transaction_id`.
  Fine for a fake gateway, but it should not be able to collide once real.

### C. Security & privacy

- [ ] **C1 · P0 ([#33](https://github.com/themiladmirza/akbarfood/issues/33)) — Business permits are stored on the public disk.**
  `RestaurantController.php:16` stores `permit_scan` — a legal licence document — via
  `->store('permits', 'public')`. Once `storage:link` exists (B7) anyone who guesses the filename can
  read it. Menu images are fine public; permits are not.
  *Fix:* private disk + a signed-URL route restricted to operators.

- [ ] **C2 · P1 ([#34](https://github.com/themiladmirza/akbarfood/issues/34)) — No rate limiting anywhere.**
  No `throttle` middleware in `routes/api.php` or `bootstrap/app.php`. `POST /api/auth/request-otp`
  is unauthenticated and triggers an outbound HTTP call. The per-phone cooldown lives in the DB, but
  nothing stops a caller cycling through thousands of different phone numbers.

- [ ] **C3 · P2 — The OTP endpoint is a user-enumeration oracle.**
  `PhoneAlreadyRegisteredException` tells an anonymous caller whether a given phone is registered.
  Fixing B1 removes this by making the response identical either way.

- [ ] **C4 · P2 — `created_at` / `updated_at` are in every `$fillable`.**
  Mass-assignment surface with no benefit — Eloquent manages both.

- [ ] **C5 · P2 — The public menu endpoint returns raw Eloquent.**
  `MenuController::listMenuItems` exposes every column, including unavailable items, to anonymous
  callers.

- [ ] **C6 · P3 — `users` has no timestamps.**
  `User::$timestamps = false` and the migration defines none, so there is no record of when an
  account was created — awkward for any later abuse investigation.

### D. API contract

- [ ] **D1 · P1 ([#55](https://github.com/themiladmirza/akbarfood/issues/55)) — No API Resources.** The database schema *is* the public contract. Rename a column
  and every client breaks. `app/Http/Resources/` does not exist. This is also what forces A7.
- [ ] **D2 · P2 — Persian display labels used as JSON keys.** `'نام رستوران:'` and `'منو:'`
  (`MenuController.php:39-40`), `'پرداخت'` (`PaymentController.php:39`). Presentation copy has become
  payload structure — clients cannot parse this stably.
- [ ] **D3** — *moved to section F, which covers naming and namespacing in full.*
- [ ] **D4 · P2 — No pagination** on `GET /api/order`, `GET /api/restaurants/pending`, or the public
  menu.
- [ ] **D5 · P3 — `Payment::Order()` is not camelCase** (PSR-1). It works only because PHP method
  names are case-insensitive — Pint will not catch this.
- [ ] **D6 · P3 — `Cart::$fillable` and `CartItem::$fillable` are `public`**; every other model uses
  `protected`.

### E. Testing, ops, schema hygiene

- [ ] **E1 · P0 ([#36](https://github.com/themiladmirza/akbarfood/issues/36)) — Zero tests.** `tests/` holds only the two stock Laravel examples. This is the
  sharpest gap: the architecture is *built* for testability — ports, DTOs, injected collaborators —
  and nothing uses those seams. B2 and B3 are each one feature test away from being caught.
- [ ] **E2 · P1 ([#52](https://github.com/themiladmirza/akbarfood/issues/52)) — Notifications block the request.** `RequestPhoneNumberVerificationService.php:47`
  calls Telegram synchronously with `->throw()` *after* the OTP row is written — so if Telegram is
  slow or down the user gets a 500 while a valid, cooldown-consuming OTP sits in the table.
  `ApproveRestaurantService` does the same. `QUEUE_CONNECTION=database` and the jobs table already
  exist; nothing is ever queued.
- [ ] **E3 · P2 — nginx config mount lost `:ro` again.** `docker-compose.yml:27`. Third time; likely
  PhpStorm's YAML formatter.
- [ ] **E4 · P3 — Migrations amend instead of describe.** `create_restaurants` adds
  `management_full_name` and `phone` and the next migration drops both; `otps.name` becomes
  `payload`. The `create_*` files no longer tell you what the tables look like.
- [ ] **E5 · P3 — Dead schema.** `restaurant_users.role` includes `'manager'` (never written or
  checked); `restaurants.status` includes `'rejected'` (unreachable — see B18).
- [ ] **E6 · P3 — Two guards, two behaviours.** `OperatorMiddleware` calls `$user->refresh()`;
  `AdminMiddleware` does not.

### F. Naming & namespace separation

Short answer: **the URL namespace is not separated at all, and the code namespace is separated
along two different axes at once.**

#### F.1 — URL paths

Current surface, in registration order:

| Method | Path | Problem |
|---|---|---|
| `POST` | `/auth/request-otp` | ✅ properly namespaced |
| `POST` | `/auth/verify-otp` | ✅ |
| `POST` | `/restaurant-register` | outside the `restaurants` namespace |
| `POST` | `/users/{userId}/promote-operator` | RPC verb; `{userId}` unbound |
| `GET` | `/restaurants/pending` | static segment squatting in the `{restaurant}` slot |
| `PATCH` | `/restaurants/{restaurant}/approve` | ✅ nested, bare verb |
| `PATCH` | `/restaurants/{restaurant}/reject` | ✅ |
| `POST` | `/restaurants/{restaurant}/add_menu_item` | verb in path + snake_case |
| `PUT` | `/restaurants/{restaurant}/update_menu_item/{menuItem}` | verb in path, twice-stated |
| `DELETE` | `/restaurants/{restaurant}/remove_menu_item/{menuItem}` | verb in path, twice-stated |
| `POST` | `/restaurants/{restaurant}/add_item_to_cart` | cart action in the restaurant namespace |
| `GET` | `/cart` | ✅ singleton |
| `PUT` | `/cart/items/{cartItem}` | ✅ (method should be `PATCH`) |
| `DELETE` | `/cart/items/{cartItem}` | ✅ |
| `POST` | `/create_order` | verb in path; no collection |
| `GET` | `/order` | singular name for a collection |
| `PUT` | `/{order}/cancel_order` | **id at the root** |
| `POST` | `/{order}/payment` | **id at the root** |
| `POST` | `/{payment}/verify_payment` | **id at the root** |
| `GET` | `/restaurants/{restaurant}/menu` | ✅ — but declared outside the auth group, far from its siblings |

- [ ] **F1 · P1 ([#53](https://github.com/themiladmirza/akbarfood/issues/53)) — Three resource ids live directly under `/api` with no collection segment.**
  `/api/{order}/cancel_order`, `/api/{order}/payment`, `/api/{payment}/verify_payment`. `{order}` and
  `{payment}` occupy the *same* URL position, so `/api/5/payment` and `/api/5/verify_payment` mean
  two different entities — the path gives the reader no way to tell what `5` is. It also sits in the
  same slot as the flat routes `/api/cart`, `/api/order`, `/api/create_order`; today nothing collides
  only because Laravel matches in registration order.
  *Fix:* `/api/orders/{order}/cancel`, `/api/orders/{order}/payments`, `/api/payments/{payment}/verify`.

- [ ] **F2 · P2 — Three casing conventions in one file.**
  kebab (`request-otp`, `restaurant-register`, `promote-operator`), snake (`add_menu_item`,
  `create_order`, `cancel_order`, `verify_payment`), and bare (`cart`, `order`, `menu`, `pending`,
  `approve`). Pick kebab-case — it is the Laravel and web convention — and apply it everywhere.

- [ ] **F3 · P2 — The verb is in the path *and* the HTTP method.**
  `POST .../add_menu_item` says "add" twice; `DELETE .../remove_menu_item` says "delete" twice.
  Compare `PATCH .../approve` and `.../reject`, which get it right. `restaurant-register` and
  `create_order` are the same mistake at the root.
  *Fix:* `POST /restaurants/{restaurant}/menu-items`, `PUT|DELETE /restaurants/{restaurant}/menu-items/{menuItem}`,
  `POST /restaurants`, `POST /orders`. State transitions (`approve`, `reject`, `cancel`, `verify`)
  may keep a verb segment — that is the one legitimate exception.

- [ ] **F4 · P2 — Singular/plural is inconsistent.**
  `restaurants` and `users` are plural; `order` is singular but returns a collection; `cart` is
  correctly singular (a singleton); `menu` is singular for a list of items. `GET /api/order` should
  be `GET /api/orders`, and `.../menu` is arguably `.../menu-items`.

- [ ] **F5 · P2 — `restaurants/*` is defined in four separate places.**
  `routes/api.php:25` (operator prefix group), `:31` (owner prefix group), `:36` (bare, inside the
  auth group), `:49` (bare, outside it). You cannot see the resource's full surface without reading
  the whole file, and the public menu route is 13 lines below the auth group it does not belong to.
  *Fix:* one `Route::prefix('restaurants')` block with nested middleware groups inside it.

- [ ] **F6 · P1 ([#54](https://github.com/themiladmirza/akbarfood/issues/54)) — The same `{restaurant}` placeholder is a model in some routes and a string in others.**
  `MenuController` type-hints `Restaurant $restaurant`, so implicit binding fires and
  `MenuItemRequest.php:29` reads `$this->route('restaurant')->id`. `CartItemController::addItemToCart`
  has **no** `Restaurant` parameter, so no binding fires and `AddItemToCartRequest.php:22` reads
  `$this->route('restaurant')` as a raw string. Both work — by accident.
  *Consequence:* the day someone adds `Restaurant $restaurant` to `addItemToCart` for any reason, the
  `Rule::exists(...)->where('restaurant_id', <Restaurant model>)` silently starts comparing a column
  to a stringified model and the scoping check breaks with no error. Bind it everywhere, or nowhere.

- [ ] **F7 · P3 — HTTP methods do not match semantics.**
  `PUT /{order}/cancel_order` is a state transition, not a replacement → `PATCH` or `POST`.
  `PUT /cart/items/{cartItem}` sends only `quantity` → `PATCH`. `PUT .../update_menu_item/...` has
  `image` as `sometimes`, so it is also partial → `PATCH`.

- [ ] **F8 · P3 — No versioning and no named routes.**
  There is no `/api/v1/` prefix, and not one route carries `->name()`. Combined with the absence of
  API Resources (D1), nothing about the contract is pinned down.

- [ ] **F9 · P3 — `restaurants/pending` is a filter wearing a resource's clothes.**
  It is "restaurants where status = pending", i.e. `GET /api/restaurants?status=pending`. As a static
  segment it also sits in the `{restaurant}` slot; it resolves only because it is registered first
  and ids are numeric.

#### F.2 — PHP namespaces

- [ ] **F10 · P1 ([#50](https://github.com/themiladmirza/akbarfood/issues/50)) — The ring boundary is invisible in the folder tree.**
  This is the one that matters most for the onion goal. `app/Services/` currently holds three
  different rings side by side:
  ```
  app/Services/CreateOrderService.php          ← Application (use case)
  app/Services/NotificationService.php         ← Application (port / interface)
  app/Services/TelegramNotificationService.php ← Infrastructure (adapter)
  ```
  Nothing in the path tells you which is which, so the dependency rule cannot be checked by reading
  the tree — or enforced by any tool.
  *Fix:* separate the adapter out, e.g. `App\Infrastructure\Notification\TelegramNotificationService`,
  and keep ports beside the use cases that own them.

- [ ] **F11 · P1 ([#51](https://github.com/themiladmirza/akbarfood/issues/51)) — `NotificationService` reuses the use-case suffix for a port.**
  Every other `*Service` is a use case with `execute()`. This one is an interface with `send()`. Same
  suffix, different contract — the name actively teaches the wrong thing.
  *Fix:* `Notifier`, `NotificationGateway`, or `SmsGateway`. Reserve `*Service` for use cases.

- [ ] **F12 · P2 — Stuttering model namespaces, applied inconsistently.**
  `App\Models\Cart\Cart`, `App\Models\Menu\Menu`, `App\Models\Order\Order`,
  `App\Models\Payment\Payment`, `App\Models\Restaurant\Restaurant` — a folder named after its only
  meaningful class says the word twice. Meanwhile `Otp` and `User` sit at `App\Models\` root, so the
  folder uses two conventions at once.
  *This already caused a real bug:* the stutter makes `App\Models\Restaurant` look like a valid class
  name, which is exactly what `config/auth.php:3` imports (see **B5**).
  *Fix:* flatten to `App\Models\Restaurant`, `App\Models\Order`, `App\Models\OrderItem`, … Group by
  aggregate only if a folder holds several classes (`Order/Order.php` + `Order/OrderItem.php` is
  defensible; `Payment/Payment.php` alone is not).

- [ ] **F13 · P2 — Two shadowed framework class names.**
  `App\Exceptions\ModelNotFoundException` shadows `Illuminate\Database\Eloquent\ModelNotFoundException`,
  and `App\Exceptions\AuthorizationException` shadows `Illuminate\Auth\Access\AuthorizationException`.
  Both framework originals are thrown by code this project calls (`findOrFail`, Gate checks). A wrong
  or missing `use` statement produces a `catch` that never fires, with no error anywhere.
  *Fix:* rename to something domain-specific — `MenuItemNotInRestaurantException`,
  `NotOrderOwnerException`.

- [ ] **F14 · P2 — Feature grouping applied to three trees and not the other three.**
  `Controllers/`, `Requests/`, `Models/` are grouped by feature; `DTOs/` (15 files), `Services/`
  (20 files) and `Exceptions/` (15 files) are flat. Same codebase, two organising principles.

- [ ] **F15 · P3 — Singular vs plural folder names collide across trees.**
  `Http/Controllers/Restaurants/` is the only plural controller folder (`Auth`, `Cart`, `Menu`,
  `Operator`, `Order`, `Payment` are all singular) — and its sibling `Http/Requests/Restaurant/` is
  singular. Same concept, two spellings.

- [ ] **F16 · P3 — Two request classes are not namespaced.**
  `SendOtpRequest` and `VerifyOtpRequest` sit at `App\Http\Requests\` root while every other request
  lives in `Requests\Cart\`, `Requests\Menu\`, `Requests\Restaurant\`. They belong in
  `Requests\Auth\`, matching `Controllers\Auth\`.

- [ ] **F17 · P3 — One controller folder is grouped by actor, not resource.**
  `Controllers/Operator/RestaurantApprovalController` groups by *who calls it*; every other folder
  groups by *what it acts on*. Approving a restaurant is a restaurant concern — and
  `OperatorController` (which promotes users) is really a *user* concern. Two axes in one tree.

- [ ] **F18 · P3 — Controller methods are plural but act on one row.**
  `addMenuItems`, `updateMenuItems`, `removeMenuItems` each handle a single item (`listMenuItems` is
  correctly plural). `sendRequestPayment` is verb-verb-noun, and `getApprovalPendingRegister` does
  not parse — `listPendingRestaurants` says it.

- [ ] **F19 · P3 — DTO property casing is mixed.**
  `menu_id`, `is_available`, `permit_scan`, `landline_number`, `vendor_type` (snake) against
  `userId`, `restaurantId` (camel). The snake ones are DB column names copied upward — the Active
  Record leak showing up in the Application ring. Pint will not fix this.

- [ ] **F20 · P3 — Middleware alias casing.**
  `bootstrap/app.php:19-21` registers `operator`, `admin`, `restaurantOwner`. The third is camelCase
  where the others are lowercase; convention is `restaurant-owner`.

---

## 4. Endpoint walkthrough

Order is the sequence you would actually call them in. All paths are prefixed `/api`.

| # | Endpoint | Actor | Findings hit |
|---|---|---|---|
| 1 | `POST /auth/request-otp` | anon | **B1**, **B6**, C2, C3, E2 |
| 2 | `POST /auth/verify-otp` | anon | B13 |
| 3 | `POST /users/{userId}/promote-operator` | admin | **B1** (no admin token obtainable), A5, A13 |
| 4 | `POST /restaurant-register` | user | **C1**, A6, A11, B7, B12 |
| 5 | `GET /restaurants/pending` | operator | A5, D1, D4 |
| 6 | `PATCH /restaurants/{restaurant}/approve` | operator | A5, B13, B19, E2 |
| 7 | `PATCH /restaurants/{restaurant}/reject` | operator | A5, **B18**, B13 |
| 8 | `POST /restaurants/{restaurant}/add_menu_item` | owner | A2, A5, A6, B7, B12 |
| 9 | `GET /restaurants/{restaurant}/menu` | **public** | **C5**, D1, D4 |
| 10 | `PUT /restaurants/{restaurant}/update_menu_item/{menuItem}` | owner | A2, A5, A9, **B3** (price edit) |
| 11 | `DELETE /restaurants/{restaurant}/remove_menu_item/{menuItem}` | owner | A5, A9 |
| 12 | `POST /restaurants/{restaurant}/add_item_to_cart` | customer | **A1**, **B9**, B10, B8 |
| 13 | `GET /cart` | customer | A7, **B3**, **B12**, B14 |
| 14 | `PUT /cart/items/{cartItem}` | customer | **A1**, A9, B12, B17 |
| 15 | `DELETE /cart/items/{cartItem}` | customer | **A1**, A9, B12 |
| 16 | `POST /create_order` | customer | **B3**, **B8**, B11, B12, B16 |
| 17 | `GET /order` | customer | D1, D4 |
| 18 | `PUT /{order}/cancel_order` | customer | **B2**, A3, A5, D3 |
| 19 | `POST /{order}/payment` | customer | A3, A5, **B12** (dead end), D3 |
| 20 | `POST /{payment}/verify_payment` | customer | **B2**, A3, A8, B20 |

Two endpoints are effectively unreachable as shipped: **#20** (no way to learn the payment id) and
**#3** (no way to get an admin token).

---

## 5. Suggested order of work

73 findings is not a backlog you work top-to-bottom. The order below follows eight rules; when in
doubt, re-derive from these rather than from severity alone.

1. **Nothing before the app runs.** A finding you cannot reproduce is a guess.
2. **Nothing irreversible before tests.** Refactors need a safety net; bugfixes are the net's first
   test cases.
3. **Renames before new code.** A rename costs the same today and more every week.
4. **Decisions before the code that depends on them.** A4 is the live example.
5. **Inner rings before outer.** Domain → Application → Delivery. Outer code depends on inner
   shapes, so doing it the other way means writing the outer layer twice.
6. **One idea per PR.** Five repetitions of one lesson beat one PR containing five lessons.
7. **Batch the breaking changes.** The public contract changes once, not five times.
8. **A bug that closes as a side effect is not a step.** B2, B3, B16, B19, C3 and D3 never get their
   own PR — they fall out of work listed below.

---

### Act I — Stabilise (nothing architectural yet)

| # | Theme | Items | Why here |
|---|---|---|---|
| 1 | Make it run | B4, B5, B6, B7, E3 | Five unrelated one-liners. Until these land, nobody can execute a single authenticated request. |
| 2 | Data integrity | B9, B10, B20 | One migration adding the missing unique indexes. Cheap, and every day without it accumulates data that later steps must clean up. |
| 3 | Security exposure | C1, C2 | Business permits are world-readable and the OTP endpoint is unthrottled. Small, independent, and a real exposure — do not bury it behind a refactor. |
| 4 | Login | B1 | Its own PR: it is a product decision (signup and login become one flow), not a fix. **C3 closes with it.** |
| 5 | Safety net | E1 | Characterisation tests for order → payment, cart totals, and the OTP flow. **B2 and B3 go in as failing tests** — they get fixed in Act II, not here. |

### Act II — The domain ring

| # | Theme | Items | Why here |
|---|---|---|---|
| 6 | Names before building | F12, F13, F15, F16 | Pure renames, IDE-assisted, no behaviour change. F13 removes two latent shadowing bugs and F12 removes the trap that caused B5. Doing it now means every later phase lands in the right place. |
| 7 | Decide A4 | A4 | A decision, possibly zero code. **Blocks step 9** — once entities enforce their own preconditions, the innermost ring starts importing `Illuminate\Http`. Settle it while nothing depends on it. |
| 8 | Rich model: predicates | A15 (part 1) | `Cart::total()`, `Order::canBePaid()`, `Restaurant::isApproved()`, `Otp::matches()`. Reads only, no writes — a safe, reviewable diff. **B2, B3 and B16 close here.** |
| 9 | Enums | A10 | Deliberately *after* step 8: once predicates exist, each status literal lives in exactly one entity method, so this becomes a small diff instead of a rewrite of seven services. |
| 10 | Rich model: transitions | A15 (part 2) | `Order::cancel()`, `Order::markPaid()`, `Restaurant::approve()`. Touches every mutation site — which is why it needs step 5's tests and step 7's decision first. |

### Act III — The application ring

| # | Theme | Items | Why here |
|---|---|---|---|
| 11 | Make the DTO a complete command | A9, A14, A1, A2, A3, A6 | One coherent pass, not six. A1/A2/A3 *are* failure modes ②/①/④ of A9 — fixing them separately would touch the same 11 files three times. Mechanical once the shape is agreed. |
| 12 | Authorization inward | A5 | Only possible after step 11 supplies `actorId` and step 8 supplies `User::owns()`. Deletes three duplicated ownership checks; middleware stays as cheap early rejection. |
| 13 | Ports, adapters, async | A8, F10, F11, E2 | All one theme: what talks to the outside world. Payment gets a port like notifications already has, the Telegram adapter moves out of `Services/`, and both sends move onto the queue. |

### Act IV — The delivery ring and the gaps

| # | Theme | Items | Why here |
|---|---|---|---|
| 14 | The v1 contract, once | F1–F9, D1, D2, D4, A7, C5 | Every breaking change to the public surface in a single PR: `/api/v1`, REST paths, API Resources, pagination. **D3 and A7 close here.** Doing it last means the shape is already settled by Acts II–III. |
| 15 | Domain gaps | B8, B18, B11 | **B8 needs design before code** — an order having no restaurant implies a decision about whether carts are per-restaurant. Treat it as its own small design round, not a ticket. |
| 16 | Long tail | F14, F17–F20, C4, C6, D5, D6, E4, E5, E6, B12–B15, B17 | Severity order. Several are already gone by now. |

---

### The critical path

Most of this is parallelisable. Only one chain is strictly ordered:

```
app runs (1) → tests (5) → predicates (8) → enums (9) → transitions (10)
                                  ↓
                   complete command (11) → authorization inward (12)
                                  ↓
                          A4 decided (7) ─┘
```

Acts I steps 2–4, and Act III step 13, can be done by anyone at any time.

### Where to start

Steps 8 and 9 on **one entity only** — give `Order` its `OrderStatus` enum plus `canBePaid()` and
`canBeCancelled()`, and watch B2 stop being a missing `if` and become impossible to write. Then
repeat the identical shape on `Cart`, `Restaurant`, `Otp`, `Payment`. Five repetitions of one idea,
each a small PR — which is rule 6 in practice.

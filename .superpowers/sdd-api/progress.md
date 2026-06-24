# SDD Progress — feat/backend-api-plan

Plan: /Users/semihduman/.claude/plans/backend-api-plan.md
Branch: feat/backend-api-plan
Merge base: 1a078b2
Baseline tests: 333

## Tasks

- [x] Task 1: Coupon list + admin CRUD (commits 1a078b2..894ee39, review clean — minor: start_date NOT NULL so no null-exclusion bug; admin route pattern matches existing project style)
- [x] Task 2: Favorites module (commits 894ee39..abeb760, review clean after fix — is_favorited overlay injected post-cache in index/show)
- [x] Task 3: Reviews module (commits abeb760..135b822, review clean — minor: no DB transaction in SubmitReviewUseCase; average_rating/review_count always 0 in product list due to omitted withAvg/withCount)
- [x] Task 4: Subscriptions module (commits 135b822..ea84fa7, review clean after fix — N+1 on variant.product resolved, factories created for ProductVariant+Address)
- [x] Task 5: Subscription cron job (commits ea84fa7..1e992db, review clean after fixes — pause_until filter added to findDueToday, DB transaction wraps order creation)
- [ ] Task 6: FAQ module + ProductResource enhancements

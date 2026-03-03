# Performance Inspector Module - Usage Examples

## Real-World Usage Scenarios

This guide demonstrates practical use cases for the Performance Inspector module.

---

## Example 1: Identifying Slow Page Loads

### Scenario

Your article list page is loading slowly. You need to find the bottleneck.

### Solution Steps

**Step 1: Enable all metrics**

- Edit Performance Inspector module
- Go to Basic tab
- Ensure all display options are "Yes"
- Save & Close

**Step 2: Navigate to article list**

- Go to **Components > Articles > Articles**

**Step 3: Monitor metrics**
Panel shows:

- Execution Time: 2500 ms (⚠️ Concerning)
- Database Queries: 145 queries (⚠️ Too many)
- Slowest Query: 250ms

**Step 4: Analysis**

- High execution time indicates page load issue
- High query count suggests N+1 queries
- Slow query may be unoptimized JOIN

**Step 5: Optimization**

- Check query logs
- Add database indexes
- Review component code for N+1 patterns

---

## Example 2: Memory Leak Detection

### Scenario

Application memory usage increasing over time. Possible memory leak.

### Solution Steps

**Step 1: Enable memory monitoring**

- Edit Performance Inspector
- Basic tab: "Show Memory Usage" = Yes
- Advanced tab: "Debug Mode" = Yes

**Step 2: Open dashboard**

- Log in to admin
- Observe module panel

**Step 3: Perform repeated actions**

- Create articles
- Upload files
- Run imports
- Observe memory readings

**Step 4: Monitor peak memory**

```
Initial visit: Current 32MB | Peak 40MB
After 10 actions: Current 45MB | Peak 65MB
After 20 actions: Current 58MB | Peak 120MB  ← Growing!
```

**Step 5: Identify leak**

- Peak memory shouldn't grow continuously
- Indicates objects not being garbage collected
- Check component code for circular references

---

## Example 3: Cache Efficiency Analysis

### Scenario

Want to verify cache is working effectively.

### Solution Steps

**Step 1: Configure module**

- Edit Performance Inspector
- Basic tab: "Show Cache Information" = Yes
- Basic tab: "Show Execution Time" = Yes

**Step 2: Clear cache**

- **System > Clear Cache**
- Select "All"
- Click "Delete"

**Step 3: Load dashboard first time**

```
Shown in module:
Cache Hits: 0
Cache Misses: 45
Hit Ratio: 0%
Execution Time: 850ms
```

**Step 4: Reload same page**

```
Shown in module:
Cache Hits: 45
Cache Misses: 0
Hit Ratio: 100%
Execution Time: 250ms
```

**Step 5: Analysis**

- Cache hits increasing = Cache working ✅
- Execution time reduced = Cache effective ✅
- Hit ratio > 90% is good performance

---

## Example 4: Database Query Optimization

### Scenario

Component has excessive database queries. Need to optimize.

### Solution Steps

**Step 1: Enable database monitoring**

- Edit Performance Inspector
- Basic tab: "Show Database Queries" = Yes
- Basic tab: "Show Slowest Query" = Yes
- Advanced tab: "Debug Mode" = Yes

**Step 2: Access problematic page**
Navigate to the slow page

**Step 3: Check module panel**

```
Database Queries: 175 queries
Query Time: 450ms

Slowest Query: SELECT * FROM jos_articles a
LEFT JOIN jos_users u ON a.created_by=u.id
LEFT JOIN jos_categories c ON a.catid=c.id
WHERE a.id IN (SELECT article_id FROM jos_relations)

Duration: 150ms
```

**Step 4: Analyze query**

- Multiple JOIN operations
- Subquery in WHERE clause
- Too many results returned

**Step 5: Optimize**
Rewrite query:

```sql
SELECT a.id, a.title, u.name, c.title
FROM jos_articles a
LEFT JOIN jos_users u ON a.created_by = u.id
LEFT JOIN jos_categories c ON a.catid = c.id
LEFT JOIN jos_relations r ON a.id = r.article_id
GROUP BY a.id
```

**Step 6: Verify improvement**

- Reload page
- Check module metrics
- Query time should decrease by 50%+

---

## Example 5: Performance Testing During Development

### Scenario

Developing new feature. Want to ensure no performance regression.

### Solution Steps

**Step 1: Set baseline**

- Enable full debug mode (all metrics + Debug Mode)
- Document initial metrics before feature development

```
Recorded Baseline:
- Execution: 400ms
- Queries: 25
- Memory: 28MB
- Cache Hit: 92%
```

**Step 2: Develop feature**

- Add new functionality
- Test through admin interface

**Step 3: Check metrics**
After development:

```
Current Metrics:
- Execution: 420ms ✅ (5% increase, acceptable)
- Queries: 26 ✅ (1 extra, expected)
- Memory: 30MB ✅ (2MB increase, normal)
- Cache Hit: 85% ⚠️ (7% decrease, needs investigation)
```

**Step 4: Analyze regression**

- Cache hit decreased - feature may be bypassing cache
- Review new code for cache interactions
- Add caching if needed

**Step 5: Optimize if needed**

- Add cache keys for new data
- Test again
- Ensure metrics stay near baseline

---

## Example 6: Monitoring During Peak Hours

### Scenario

Monitor site performance when many administrators are active.

### Solution Steps

**Step 1: Enable light monitoring**

- Basic tab: All options = Yes
- Advanced tab: Debug Mode = No (reduces overhead)
- Update Interval: 3000ms (refresh every 3 seconds)

**Step 2: Keep dashboard open**

- Open admin dashboard in separate browser tab
- Keep Performance Inspector in view

**Step 3: Observe during active period**

```
Time | Execution | Queries | Memory | Cache Hit
9:00 | 350ms | 28 | 32MB | 88%
9:15 | 425ms | 42 | 38MB | 82%
9:30 | 550ms | 65 | 45MB | 71% ← Performance degrading
9:45 | 720ms | 89 | 52MB | 45% ← Critical point
```

**Step 4: Identify patterns**

- Performance increases with user activity
- Cache hit ratio drops when many users active
- Suggests cache contention or insufficient cache size

**Step 5: Recommendations**

- Increase cache size
- Implement user-specific cache keys
- Distribute load across multiple servers

---

## Example 7: Component Troubleshooting

### Scenario

Component causing site issues. Need to identify root cause.

### Solution Steps

**Step 1: Full diagnostics**

- All display options = Yes
- Debug Mode = Yes
- Keep module position in easy view (top-right)

**Step 2: Access component**

- Navigate to suspected component
- Monitor metrics

**Step 3: Identify anomalies**

```
Component A (Normal):
- Execution: 400ms ✅
- Queries: 25 ✅
- Memory: 28MB ✅

Component B (Problematic):
- Execution: 3200ms ⚠️
- Queries: 450 ⚠️
- Memory: 120MB ⚠️
- Slowest Query: 800ms (SELECT ALL without LIMIT)
```

**Step 4: Root cause analysis**

- Excessive queries with no limits
- Complex queries without optimization
- Memory-intensive operations

**Step 5: Report findings**

```
Component B Performance Issues:
1. Unoptimized queries (no LIMIT clauses)
2. N+1 queries in article loop
3. Missing database indexes
4. Inefficient JOIN operations

Recommendations:
- Add LIMIT to queries
- Implement batch loading
- Create database indexes
- Refactor JOIN logic
```

---

## Example 8: Cache System Validation

### Scenario

Uncertain if cache system is properly configured.

### Solution Steps

**Step 1: Enable cache monitoring**

- Show Cache Information = Yes
- Debug Mode = Yes

**Step 2: Check cache handler**
Debug info shows:

```
Cache Handler: File  (or Redis/APCu/etc)
```

**Step 3: Verify cache functionality**

- Clear all cache
- Observe Cache Hits/Misses
- Should see increase in cache hits on repeat visits

**Step 4: Expected behavior**

```
Visit 1: 0 hits, 45 misses (cold cache)
Visit 2: 45 hits, 0 misses (warm cache)
Visit 3: 45 hits, 0 misses (cache working)
```

**Step 5: If cache not working**

- Check Global Configuration > Cache Settings
- Verify cache handler is enabled
- Check directory permissions (for file cache)
- Review error logs

---

## Example 9: Before/After Performance Comparison

### Scenario

Made optimization changes. Want to verify improvement.

### Solution Steps

**Step 1: Record baseline (Before)**

```
BEFORE Optimization:
Execution Time: 850ms
Database Queries: 95
Query Time: 320ms
Memory: 48MB
Slowest Query: 180ms
```

**Step 2: Implement optimization**

- Add caching layer
- Optimize queries
- Update database indexes

**Step 3: Measure improvement (After)**

```
AFTER Optimization:
Execution Time: 450ms (-47% improvement ✅)
Database Queries: 32 (-66% reduction ✅)
Query Time: 85ms (-73% improvement ✅)
Memory: 35MB (-27% reduction ✅)
Slowest Query: 25ms (-86% improvement ✅)
```

**Step 4: Calculate impact**

- Page load time halved
- Server capacity doubled
- Better user experience
- Reduced server costs

---

## Example 10: Development vs Production Comparison

### Scenario

Want to understand performance differences between environments.

### Solution Steps

**Step 1: Development environment metrics**
Connect via debug mode:

```
DEV (Local):
- Execution: 250ms
- Queries: 35
- Cache Handler: File
- Debug Mode: Yes
```

**Step 2: Production environment metrics**
Have admin check production:

```
PROD (Server):
- Execution: 180ms
- Queries: 28
- Cache Handler: Redis
- Debug Mode: No
```

**Step 3: Analysis**

- Production faster due to better caching (Redis)
- Fewer queries in production (production data)
- Debug mode off reduces overhead
- Dev should mimic prod configuration

**Step 4: Optimization recommendations**

- Use Redis locally for development
- Disable debug mode during testing
- Use production-like data
- Test with same cache configuration

---

## Tips & Best Practices

### Performance Tips

1. **Monitor regularly** - Check metrics during development
2. **Set baselines** - Know your normal ranges
3. **Investigate anomalies** - Unexpected metrics indicate issues
4. **Test incrementally** - Change one thing at a time
5. **Use debug mode selectively** - Disable when not needed

### Optimization Order

1. Identify slowest queries first
2. Add database indexes
3. Reduce query count (eliminate N+1)
4. Optimize memory usage
5. Improve cache hit ratio

### Monitoring Schedule

- **Development**: Continuous monitoring
- **Before Release**: Full performance testing
- **After Release**: Weekly checks
- **Maintenance**: Monthly performance audit

---

## Common Metrics Reference

| Metric           | Good   | Acceptable | Concerning |
| ---------------- | ------ | ---------- | ---------- |
| Execution Time   | <500ms | 500-1000ms | >1000ms    |
| Database Queries | <30    | 30-80      | >80        |
| Query Time       | <100ms | 100-300ms  | >300ms     |
| Memory Usage     | <32MB  | 32-64MB    | >64MB      |
| Cache Hit Ratio  | >90%   | 70-90%     | <70%       |

---

**Last Updated**: March 3, 2024  
**Module Version**: 5.0.0

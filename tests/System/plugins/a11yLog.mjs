import { mkdirSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import yaml from 'js-yaml';

/**
 * Builds a stable selector string from an axe node target. Targets inside iframes or shadow
 * DOM are arrays of selectors, they are joined with " >>> ".
 *
 * @param {Array} target The axe node target
 *
 * @returns {string}
 */
export function targetToSelector(target) {
  return (target || []).map((part) => (Array.isArray(part) ? part.join(' >>> ') : part)).join(' >>> ');
}

/**
 * Sorts the violations, so the written file is deterministic and can be diffed and used as baseline.
 *
 * @param {Array} violations The violations
 *
 * @returns {Array}
 */
function sortViolations(violations) {
  const compare = (a, b) => (a < b ? -1 : (a > b ? 1 : 0));

  violations.forEach((violation) => violation.nodes.sort((a, b) => compare(a.target, b.target)));

  return violations.sort((a, b) => compare(a.spec, b.spec) || compare(a.test, b.test) || compare(a.rule, b.rule));
}

/**
 * Registers the task and events to collect all accessibility violations found by
 * cy.runAccessibilityScan() during a run and to write them to
 * tests/System/output/a11y-violations.yml after the run.
 *
 * The file has the same format as the baseline file a11y-baseline.yml in the repository root,
 * see tests/System/a11y-compare.mjs.
 *
 * @param {*} on
 * @param {object} config The configuration
 */
export default function setupA11yLog(on, config) {
  // Key "spec|test" => { attempt, violations }
  const tests = new Map();

  on('task', {
    recordA11yViolations({ spec, test, attempt, violations }) {
      const key = `${spec}|${test}`;
      const existing = tests.get(key);

      // A retried test replaces the violations of the previous attempt
      if (!existing || existing.attempt < attempt) {
        tests.set(key, { attempt, violations: [] });
      }

      const entry = tests.get(key);

      violations.forEach(({
        id, impact, description, help, helpUrl, nodes,
      }) => {
        // Several scans in one test can report the same rule, they are merged into one entry
        let violation = entry.violations.find((item) => item.rule === id);

        if (!violation) {
          violation = {
            spec,
            test,
            rule: id,
            impact,
            description,
            help,
            helpUrl,
            nodes: [],
          };
          entry.violations.push(violation);
        }

        nodes.forEach((node) => {
          const target = targetToSelector(node.target);

          if (!violation.nodes.some((item) => item.target === target)) {
            violation.nodes.push({ target, html: node.html, failureSummary: node.failureSummary });
          }
        });
      });

      return null;
    },
  });

  on('after:run', () => {
    const violations = sortViolations([...tests.values()].flatMap((entry) => entry.violations));
    const file = join(config.projectRoot, 'tests/System/output/a11y-violations.yml');

    mkdirSync(dirname(file), { recursive: true });
    writeFileSync(file, yaml.dump({ violations }, { lineWidth: -1, noRefs: true }));

    console.log(`[a11y] Wrote ${violations.length} violations to ${file}`);
  });
}

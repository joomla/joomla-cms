/**
 * Compares the accessibility violations of a system test run against the baseline.
 *
 * Usage: node tests/System/a11y-compare.mjs <baseline.yml> <violations.yml>
 *
 * A violation is identified by spec, test, axe rule and the selector of the failing element.
 * Every violation which is not in the baseline is reported as error and the script exits with 1.
 * Violations from the baseline which were not found anymore are reported as notice, they can be
 * removed from the baseline.
 *
 * When running on GitHub Actions, annotations and a job summary are written.
 */
import { appendFileSync, existsSync, readFileSync } from 'node:fs';
import yaml from 'js-yaml';

const [baselineFile, resultFile] = process.argv.slice(2);

if (!baselineFile || !resultFile) {
  console.error('Usage: node tests/System/a11y-compare.mjs <baseline.yml> <violations.yml>');
  process.exit(2);
}

/**
 * Loads a violations file and returns a map of the identifying key to the violation details.
 *
 * @param {string} file The file path
 *
 * @returns {Map<string, object>}
 */
function load(file) {
  if (!existsSync(file)) {
    console.error(`::error title=Accessibility check::File ${file} does not exist`);
    process.exit(2);
  }

  const data = yaml.load(readFileSync(file, 'utf8')) || {};

  if (data.violations !== undefined && !Array.isArray(data.violations)) {
    console.error(`::error title=Accessibility check::${file} must contain a list "violations"`);
    process.exit(2);
  }

  const items = new Map();

  (data.violations || []).forEach((violation) => {
    (violation.nodes || []).forEach((node) => {
      const item = {
        spec: violation.spec,
        test: violation.test,
        rule: violation.rule,
        impact: violation.impact,
        help: violation.help,
        helpUrl: violation.helpUrl,
        target: node.target,
        html: node.html,
        failureSummary: node.failureSummary,
      };

      items.set(JSON.stringify([item.spec, item.test, item.rule, item.target]), item);
    });
  });

  return items;
}

/**
 * Escapes a value for a GitHub Actions workflow command.
 *
 * @param {string} value The value
 *
 * @returns {string}
 */
function escape(value) {
  return String(value ?? '').replace(/%/g, '%25').replace(/\r/g, '%0D').replace(/\n/g, '%0A');
}

/**
 * Escapes a value for a markdown table cell.
 *
 * @param {string} value The value
 *
 * @returns {string}
 */
function cell(value) {
  return String(value ?? '').replace(/\|/g, '\\|').replace(/\r?\n/g, ' ');
}

const baseline = load(baselineFile);
const result = load(resultFile);

const added = [...result].filter(([key]) => !baseline.has(key)).map(([, item]) => item);
const fixed = [...baseline].filter(([key]) => !result.has(key)).map(([, item]) => item);

added.forEach((item) => {
  const message = [
    `Journey: ${item.test}`,
    `Rule: ${item.rule} (${item.impact})`,
    `Element: ${item.target}`,
    `HTML: ${item.html}`,
    `Problem: ${item.help}`,
    item.failureSummary,
    `How to fix: ${item.helpUrl}`,
  ].filter(Boolean).join('\n');

  console.log(`::error file=${escape(item.spec)},title=${escape(`New accessibility violation: ${item.rule}`)}::${escape(message)}`);
});

fixed.forEach((item) => {
  console.log(`::notice file=${escape(item.spec)},title=${escape(`Accessibility violation fixed: ${item.rule}`)}::${escape(
    `Journey: ${item.test}\nElement: ${item.target}\nThis entry can be removed from ${baselineFile}.`,
  )}`);
});

console.log(`[a11y] ${result.size} violations found, ${baseline.size} in the baseline, ${added.length} new, ${fixed.length} fixed`);

if (process.env.GITHUB_STEP_SUMMARY) {
  const table = (items) => [
    '| Journey | Rule | Impact | Element | How to fix |',
    '| --- | --- | --- | --- | --- |',
    ...items.map((item) => `| ${cell(`${item.spec}: ${item.test}`)} | ${cell(item.rule)} | ${cell(item.impact)} | \`${cell(item.target)}\` | [${cell(item.help)}](${item.helpUrl}) |`),
  ].join('\n');

  let summary = '## Accessibility check\n\n';
  summary += `${result.size} violations found, ${baseline.size} in the baseline, ${added.length} new, ${fixed.length} fixed.\n\n`;

  if (added.length) {
    summary += `### New violations\n\n${table(added)}\n\n`;
  }

  if (fixed.length) {
    summary += `### Fixed violations (can be removed from the baseline)\n\n${table(fixed)}\n\n`;
  }

  appendFileSync(process.env.GITHUB_STEP_SUMMARY, summary);
}

process.exit(added.length ? 1 : 0);

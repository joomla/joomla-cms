# End-to-End Testing with testRigor

This directory contains the end-to-end testing infrastructure for the Joomla! project using testRigor.

## Overview

testRigor is an AI-powered end-to-end testing platform that allows you to write tests in plain English. This setup enables automated testing of the Joomla! application's user interface and functionality from a user's perspective.

## How it Works

1. **GitHub Actions**: Automated workflow in `.github/workflows/joomla-install-test.yml` that:
   - Installs the Joomla test app locally
   - Runs testRigor test suites against `localhost:8080`
   - Uses GitHub secrets for authentication (`CI_TOKEN` and `SUITE_ID`)
2. **testRigor CLI**: Executes test suites written in natural language on the testRigor platform

## Usage

### Local Testing
```bash
# Start the test app
cd tools/tests/end-to-end/test-app
meteor

# Run testRigor tests (requires valid tokens)
testrigor test-suite run <SUITE_ID> --token <CI_TOKEN> --localhost --url http://localhost:8080
```

### CI/CD Testing
Tests automatically run via GitHub Actions when:
- Pushing to `end-to-end-testing`, `main`, or `devel` branches
- Creating pull requests to `main` or `devel`
- Manual workflow dispatch

## Configuration

Set these GitHub repository secrets:
- `CI_TOKEN`: Your testRigor authentication token
- `SUITE_ID`: The testRigor test suite identifier

## Learn More

- [testRigor Documentation](https://testrigor.com/docs/)
- [testRigor CLI Reference](https://testrigor.com/command-line)

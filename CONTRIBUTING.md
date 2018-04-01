# Contributing

## Getting Started
 * Fork, then clone the repo:
```bash
git clone git@github.com:your-username/things-manager-service.git
````

 * Make sure everything goes well:
```bash
make build
make test
```

 * Make your changes (Add/Update tests according to your changes).
 * Make sure tests are still green:
```bash
make test
```

 * To check code coverage, launch
```bash
composer run test:coverage
```

 * Push to your fork and [submit a pull request](https://github.com/yoanm/things-manager-service/compare/).
 * Wait for feedback or merge.

  Some stuff that will increase your pull request's acceptance:
    * Write tests.
    * Follow PSR-2 coding style.
    * Write good commit messages.

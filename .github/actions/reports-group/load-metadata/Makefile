.PHONY: all
all: build

.PHONY: install
install:
	yarn install

.PHONY: build
build: install package

.PHONY: package
package:
	rm -rf dist/* && ./node_modules/.bin/ncc build index.js --minify --source-map --license LICENSE --out dist

.PHONY: lint
lint:
	eslint index.js

.PHONY: test
test:
	echo "Error: no test specified"
	exit 1

get-action-nodejs-version: ## Display node version configured on action.yml
	@grep -E "using:\s*'?node" action.yml | sed -e "s/^.*using: '*node\([0-9][0-9]\)'*.*$$/\1/"

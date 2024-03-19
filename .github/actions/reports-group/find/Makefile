.PHONY: configure
configure:
	$(eval version = $(shell $(MAKE) get-action-nodejs-version))
	. ~/.nvm/nvm.sh || true && nvm install $(version) && nvm use $(version)

.PHONY: install
install:
	yarn install

.PHONY: build
build: install package

.PHONY: package
package:
	npx ncc build --source-map --license LICENSE src

.PHONY: lint
lint:
	npx eslint sec

.PHONY: test
test:
	echo "Error: no test specified"
	exit 1

get-action-nodejs-version: ## Display node version configured on action.yml
	@grep -E "using:\s*'?node" action.yml | sed -e "s/^.*using: '*node\([0-9][0-9]\)'*.*$$/\1/"

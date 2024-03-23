PKG_LIST := node-gha-helpers node-sdk find-action load-metadata-action create-action codecov-uploader-action codacy-uploader-action attach-check-run-to-triggering-workflow-action fetch-workflow-metadata-action
TARGETS := configure install build package lint test

define FOR_EACH_PKG
    @for PKG in $(PKG_LIST); do \
	    echo ">>>>>> $(1) \"$$PKG\" Package >>>>>>"; \
	    $(MAKE) -C $$PKG $(1) || exit 1; \
	    echo "<<<<<<\n"; \
    done
endef

$(TARGETS):
	$(call FOR_EACH_PKG,$@)

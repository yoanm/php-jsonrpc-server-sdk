PKG_LIST := node-sdk find load-metadata create codecov-uploader codacy-uploader
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

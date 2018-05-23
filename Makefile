default: help


help: help-on-mitgliedsantrag

.PHONY: help-on-mitgliedsantrag
help-on-mitgliedsantrag:
	@echo "on-mitgliedsantrag packaging targets:"
	@echo "    deploy-deb-remote"
	@echo

.PHONY: deploy-deb-remote
deploy-deb-remote: dist-deb-packages-directory
	@if [ -z "$(DEPLOY_TARGET)" ]; then \
		echo >&2 "Missing 'DEPLOY_TARGET' environment variable (e.g. 'root@jun.on')."; \
		exit 1; fi
	scp "$(DIR_DEBIAN_SIMPLIFIED_PACKAGE_FILES)"/*.deb "$(DEPLOY_TARGET):/tmp/"
	ssh "$(DEPLOY_TARGET)" \
		'for fname in on-mitgliedsantrag; do \
			dpkg -i "/tmp/$$fname.deb" && rm "/tmp/$$fname.deb" || exit 1; done'

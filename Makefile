.PHONY: install-dependencies run

install-dependencies:
	cd docroot/themes/custom/millboard && npm install

run:
	cd docroot/themes/custom/millboard && gulp

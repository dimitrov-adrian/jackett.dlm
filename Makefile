default: build

build:
	@tar -zcf jackett.dlm INFO search.php categories.php
	@echo "Build done. Use jacket.dlm file"

tests:
	php "./test/test.php" ${ARGS}

clean:
	@rm -f jackett.dlm

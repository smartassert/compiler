#!/usr/bin/env bash

HOST_TARGET_PATH="tests/build/target"
CONTAINER_SOURCE_PATH="/app/source"
CONTAINER_TARGET_PATH="/app/tests"
HOST_PORT="8000"
CONTAINER_TEST_FILENAME="Test/example.com.verify-open-literal.yml"

nc localhost ${HOST_PORT} <<< "./compiler --version"
echo ""

COMPILER_OUTPUT=$(nc localhost ${HOST_PORT} <<< "./bin/compiler --source=${CONTAINER_SOURCE_PATH}/${CONTAINER_TEST_FILENAME} --target=${CONTAINER_TARGET_PATH}")
printf "${COMPILER_OUTPUT}\n"

if [[ $COMPILER_OUTPUT =~ (Generated.*\.php) ]]; then
  GENERATED_TEST_FILENAME=${BASH_REMATCH}
else
  echo "x generated filename extraction failed"

  return 1
fi

EXPECTED_GENERATED_FILENAME="${HOST_TARGET_PATH}/${GENERATED_TEST_FILENAME}"
OUTPUT=$(ls ${EXPECTED_GENERATED_FILENAME} | wc -l)

if [ ${OUTPUT} != "1" ]; then
  echo "x test generation failed"
  exit 1
else
  echo "✓ test generation successful"
fi

exit 0

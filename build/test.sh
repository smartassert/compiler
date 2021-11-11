#!/usr/bin/env bash

SOURCE_PATH="/input"
TARGET_PATH="/output"

HOST_DATA_PATH="$(pwd)/docker/data"
HOST_SOURCE_PATH="${HOST_DATA_PATH}${SOURCE_PATH}"
HOST_TARGET_PATH="${HOST_DATA_PATH}${TARGET_PATH}"

CONTAINER_DATA_PATH="/app/data"

CONTAINER_SOURCE_PATH="${CONTAINER_DATA_PATH}${SOURCE_PATH}"
CONTAINER_TARGET_PATH="${CONTAINER_DATA_PATH}${TARGET_PATH}"
CONTAINER_NAME="test-compiler-container"
CONTAINER_PORT="8000"
HOST_PORT="9000"
CONTAINER_TEST_FILENAME="test.yml"

IMAGE_NAME="smartassert/compiler:${TAG_NAME:-master}"

echo "Testing image: ${IMAGE_NAME}"

mkdir -p ${HOST_SOURCE_PATH}
mkdir -p ${HOST_TARGET_PATH}

cp tests/Fixtures/basil/Test/example.com.verify-open-literal.yml ${HOST_SOURCE_PATH}/${CONTAINER_TEST_FILENAME}

docker rm -f ${CONTAINER_NAME} >/dev/null 2>&1
docker create -p ${HOST_PORT}:${CONTAINER_PORT} -v ${HOST_DATA_PATH}:${CONTAINER_DATA_PATH} --name ${CONTAINER_NAME} ${IMAGE_NAME}
docker start ${CONTAINER_NAME}

sleep 0.1

( echo "./compiler --version"; ) | nc localhost ${HOST_PORT}
printf "\n"

COMPILER_OUTPUT=$( ( echo "./bin/compiler --source=${CONTAINER_SOURCE_PATH}/${CONTAINER_TEST_FILENAME} --target=${CONTAINER_TARGET_PATH}"; ) | nc localhost ${HOST_PORT} )
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

rm -Rf ${HOST_DATA_PATH}

exit 0

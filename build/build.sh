#!/usr/bin/env bash

docker build -t "smartassert/compiler:${TAG_NAME:-master}" .

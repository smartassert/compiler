<?php

declare(strict_types=1);

namespace SmartAssert\Compiler;

enum ExitCode: int
{
    case CONFIG_SOURCE_EMPTY = 100;

    case CONFIG_SOURCE_NOT_READABLE = 102;

    case CONFIG_TARGET_EMPTY = 103;

    case CONFIG_TARGET_NOT_A_DIRECTORY = 105;

    case CONFIG_TARGET_NOT_WRITABLE = 106;

    case CONFIG_SOURCE_NOT_ABSOLUTE = 107;

    case CONFIG_TARGET_NOT_ABSOLUTE = 108;

    case INVALID_YAML = 200;

    case CIRCULAR_STEP_IMPORT = 201;

    case EMPTY_TEST = 202;

    case INVALID_PAGE = 203;

    case INVALID_TEST = 204;

    case NON_RETRIEVABLE_IMPORT = 205;

    case UNPARSEABLE_DATA = 206;

    case UNKNOWN_ELEMENT = 207;

    case UNKNOWN_ITEM = 208;

    case UNKNOWN_PAGE_ELEMENT = 209;

    case UNRESOLVED_PLACEHOLDER = 211;

    case UNSUPPORTED_STEP = 212;
}

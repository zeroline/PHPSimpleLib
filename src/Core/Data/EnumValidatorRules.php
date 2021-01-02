<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Data;

final class EnumValidatorRules
{
    const REQUIRED = "checkRequired";
    const IS_EMAIL = "checkEmail";
    const IS_URL = "checkUrl";
    const IS_NUMBER = "checkNumber";
    const IS_INT = "checkInt";
    const IS_IBAN = 'checkIBAN';
    const IS_BIC = 'checkBIC';
    const IN_RANGE = "checkRange";
    const MIN = "checkMinValue";
    const MAX = "checkMaxValue";
    const STR_MIN = "checkMinStrLength";
    const STR_MAX = "checkMaxStrLength";
    const STR_LEN = "checkStrLengthRange";
    const EQUALS = "checkEqual";
    const EQUALS_NOT = "checkNotEqual";
    const IN_ARRAY = "checkInArray";
    const IS_ARRAY = "checkIsArray";
    const IS_OBJECT = "checkIsObject";
    const IS_OBJECT_OR_ARRAY = "checkIsObjectOrArray";
    const IS_VALID_JSON = "checkIsValidJsonString";
    public const FILTER_ENCODE_HTML = "filterEncodeHtml";
    public const FILTER_STRIP_HTML = "filterStripHtml";
    const CUSTOM = 'checkCustom';
}

<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Data;

final class EnumFilterModes
{
    public const FILTER_MODE_BEFORE_SAVE = 'beforeSave';
    public const FILTER_MODE_AFTER_AFTER_FETCH = 'afterFetch';
    public const FILTER_MODE_BOTH = 'both';
}

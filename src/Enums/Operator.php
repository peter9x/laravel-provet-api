<?php

declare(strict_types=1);

namespace Mupy\ProvetApi\Enums;

/**
 * Filter operators supported by the Provet Cloud API's `field__operator` query syntax.
 */
enum Operator: string
{
    case IS = 'is';
    case IS_NOT = 'is_not';
    case IN = 'in';
    case NOT_IN = 'not_in';
    case CONTAINS = 'contains';
    case CONTAINS_NOT = 'contains_not';
    case ICONTAINS = 'icontains';
    case ICONTAINS_NOT = 'icontains_not';
    case GREATER_THAN = 'gt';
    case GREATER_THAN_OR_EQUAL = 'gte';
    case LESS_THAN = 'lt';
    case LESS_THAN_OR_EQUAL = 'lte';
    case RANGE = 'range';
    case NOT_IN_RANGE = 'not_in_range';
    case IS_NULL = 'is_null';
}

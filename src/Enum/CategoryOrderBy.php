<?php

namespace App\Enum;

class CategoryOrderBy
{
    const NAME = "name";

    /**
     * @return array
     */
    public static function getAllOrderBys()
    {
        return [
            self::NAME
        ];
    }

    /**
     * @param string $lookupOrderBy
     * @return bool
     */
    public static function hasOrderBy(string $lookupOrderBy)
    {
        $hasOrderBy = false;

        foreach (self::getAllOrderBys() as $currentOrderBy) {
            if ($lookupOrderBy === $currentOrderBy) {
                $hasOrderBy = true;
                break;
            }
        }

        return $hasOrderBy;
    }

}
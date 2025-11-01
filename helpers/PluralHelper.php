<?php

namespace app\helpers;

/**
 * PluralHelper provides pluralization utilities
 */
class PluralHelper
{
    /**
     * Format posts count with correct plural form
     * Examples: "1 пост", "2 поста", "5 постов", "нет постов"
     *
     * @param int $count
     * @return string
     */
    public static function formatPostsCount($count)
    {
        if ($count == 0) {
            return 'нет постов';
        }

        $forms = ['пост', 'поста', 'постов'];
        return $count . ' ' . self::plural($count, $forms);
    }

    /**
     * Russian plural forms
     *
     * @param int $n Number
     * @param array $forms Array of 3 forms [one, few, many]
     * @return string
     */
    private static function plural($n, $forms)
    {
        $n = abs($n) % 100;
        $n1 = $n % 10;

        if ($n > 10 && $n < 20) {
            return $forms[2];
        }

        if ($n1 > 1 && $n1 < 5) {
            return $forms[1];
        }

        if ($n1 == 1) {
            return $forms[0];
        }

        return $forms[2];
    }
}

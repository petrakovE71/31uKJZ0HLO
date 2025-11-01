<?php

namespace app\helpers;

use Yii;

/**
 * TimeHelper provides time formatting utilities
 */
class TimeHelper
{
    /**
     * Format Unix timestamp as relative time
     * Examples: "только что", "минуту назад", "5 минут назад", "2 часа назад", "3 дня назад"
     *
     * @param int $timestamp Unix timestamp
     * @return string
     */
    public static function relativeFormat($timestamp)
    {
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'только что';
        }

        if ($diff < 3600) {
            // Minutes
            $minutes = floor($diff / 60);
            return self::pluralizeMinutes($minutes) . ' назад';
        }

        if ($diff < 86400) {
            // Hours
            $hours = floor($diff / 3600);
            return self::pluralizeHours($hours) . ' назад';
        }

        if ($diff < 604800) {
            // Days
            $days = floor($diff / 86400);
            return self::pluralizeDays($days) . ' назад';
        }

        if ($diff < 2592000) {
            // Weeks
            $weeks = floor($diff / 604800);
            return self::pluralizeWeeks($weeks) . ' назад';
        }

        if ($diff < 31536000) {
            // Months
            $months = floor($diff / 2592000);
            return self::pluralizeMonths($months) . ' назад';
        }

        // Years
        $years = floor($diff / 31536000);
        return self::pluralizeYears($years) . ' назад';
    }

    /**
     * Pluralize minutes (минута, минуты, минут)
     *
     * @param int $n
     * @return string
     */
    private static function pluralizeMinutes($n)
    {
        $cases = ['минуту', 'минуты', 'минут'];
        return $n . ' ' . self::plural($n, $cases);
    }

    /**
     * Pluralize hours (час, часа, часов)
     *
     * @param int $n
     * @return string
     */
    private static function pluralizeHours($n)
    {
        $cases = ['час', 'часа', 'часов'];
        return $n . ' ' . self::plural($n, $cases);
    }

    /**
     * Pluralize days (день, дня, дней)
     *
     * @param int $n
     * @return string
     */
    private static function pluralizeDays($n)
    {
        $cases = ['день', 'дня', 'дней'];
        return $n . ' ' . self::plural($n, $cases);
    }

    /**
     * Pluralize weeks (неделю, недели, недель)
     *
     * @param int $n
     * @return string
     */
    private static function pluralizeWeeks($n)
    {
        $cases = ['неделю', 'недели', 'недель'];
        return $n . ' ' . self::plural($n, $cases);
    }

    /**
     * Pluralize months (месяц, месяца, месяцев)
     *
     * @param int $n
     * @return string
     */
    private static function pluralizeMonths($n)
    {
        $cases = ['месяц', 'месяца', 'месяцев'];
        return $n . ' ' . self::plural($n, $cases);
    }

    /**
     * Pluralize years (год, года, лет)
     *
     * @param int $n
     * @return string
     */
    private static function pluralizeYears($n)
    {
        $cases = ['год', 'года', 'лет'];
        return $n . ' ' . self::plural($n, $cases);
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

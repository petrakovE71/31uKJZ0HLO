<?php

namespace app\services;

use yii\helpers\HtmlPurifier;

/**
 * HtmlSanitizerService handles HTML sanitization
 * Allows only <b>, <i>, <s> tags
 */
class HtmlSanitizerService
{
    /**
     * Sanitize HTML - allow only <b>, <i>, <s> tags
     *
     * @param string $html
     * @return string
     */
    public function sanitize($html)
    {
        $config = [
            'HTML.Allowed' => 'b,i,s',
            'AutoFormat.RemoveEmpty' => true,
        ];

        return HtmlPurifier::process($html, $config);
    }
}

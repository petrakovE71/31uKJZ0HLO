<?php

namespace app\services;

use Yii;

/**
 * IpService handles IP address operations
 */
class IpService
{
    /**
     * Get current user's IP address
     *
     * @return string
     */
    public function getCurrentIp()
    {
        return Yii::$app->request->userIP;
    }

    /**
     * Mask IP address for display
     * - IPv4: 46.211.**.** (hide last 2 octets)
     * - IPv6: 2001:0db8:****:****:****:**** (hide last 4 sections)
     *
     * @param string $ip
     * @return string
     */
    public function maskIp($ip)
    {
        if ($this->isIPv4($ip)) {
            return $this->maskIPv4($ip);
        } elseif ($this->isIPv6($ip)) {
            return $this->maskIPv6($ip);
        }

        // If unknown format, return as is
        return $ip;
    }

    /**
     * Check if IP is IPv4
     *
     * @param string $ip
     * @return bool
     */
    private function isIPv4($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    /**
     * Check if IP is IPv6
     *
     * @param string $ip
     * @return bool
     */
    private function isIPv6($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * Mask IPv4 address - hide last 2 octets
     * Example: 46.211.123.45 -> 46.211.**.**
     *
     * @param string $ip
     * @return string
     */
    private function maskIPv4($ip)
    {
        $parts = explode('.', $ip);

        if (count($parts) === 4) {
            $parts[2] = '**';
            $parts[3] = '**';
            return implode('.', $parts);
        }

        return $ip;
    }

    /**
     * Mask IPv6 address - hide last 4 sections
     * Example: 2001:0db8:11a3:09d7:1f34:8a2e:07a0:765d -> 2001:0db8:11a3:09d7:****:****:****:****
     *
     * @param string $ip
     * @return string
     */
    private function maskIPv6($ip)
    {
        // Expand IPv6 address to full format
        $expanded = inet_pton($ip);
        if ($expanded === false) {
            return $ip;
        }

        $hex = bin2hex($expanded);

        // Split into 8 sections (4 hex digits each)
        $sections = str_split($hex, 4);

        if (count($sections) === 8) {
            // Mask last 4 sections
            $sections[4] = '****';
            $sections[5] = '****';
            $sections[6] = '****';
            $sections[7] = '****';

            return implode(':', $sections);
        }

        return $ip;
    }
}

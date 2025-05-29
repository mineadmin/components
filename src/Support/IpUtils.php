<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace Mine\Support;

/**
 * Symfony IP Utils.
 * @see https://github.com/symfony/symfony/blob/7.4/src/Symfony/Component/HttpFoundation/IpUtils.php
 */
class IpUtils
{
    public const PRIVATE_SUBNETS = [
        '127.0.0.0/8',    // RFC1700 (Loopback)
        '10.0.0.0/8',     // RFC1918
        '192.168.0.0/16', // RFC1918
        '172.16.0.0/12',  // RFC1918
        '169.254.0.0/16', // RFC3927
        '0.0.0.0/8',      // RFC5735
        '240.0.0.0/4',    // RFC1112
        '::1/128',        // Loopback
        'fc00::/7',       // Unique Local Address
        'fe80::/10',      // Link Local Address
        '::ffff:0:0/96',  // IPv4 translations
        '::/128',         // Unspecified address
    ];

    private static array $checkedIps = [];

    /****
 * Prevents instantiation of the IpUtils class.
 */
    private function __construct() {}

    /****
     * Determines if an IP address is contained within any of the specified IPs or subnets.
     *
     * Supports both IPv4 and IPv6 addresses and subnets in CIDR notation. Returns true if the given IP matches or falls within any entry in the provided list.
     *
     * @param array|string $ips One or more IP addresses or subnets to check against.
     * @return bool True if the IP is contained in any of the specified IPs or subnets; otherwise, false.
     */
    public static function checkIp(string $requestIp, array|string $ips): bool
    {
        if (! \is_array($ips)) {
            $ips = [$ips];
        }

        $method = mb_substr_count($requestIp, ':') > 1 ? 'checkIp6' : 'checkIp4';

        foreach ($ips as $ip) {
            if (self::$method($requestIp, $ip)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determines if an IPv4 address matches or is contained within a given IPv4 address or subnet.
     *
     * Supports checking against a single IPv4 address or a subnet in CIDR notation. Returns true if the request IP matches the address or falls within the specified subnet; otherwise, returns false. Invalid IPs or subnets result in false.
     *
     * @param string $requestIp The IPv4 address to check.
     * @param string $ip IPv4 address or subnet in CIDR notation.
     * @return bool True if the request IP matches or is within the subnet; false otherwise.
     */
    public static function checkIp4(string $requestIp, string $ip): bool
    {
        $cacheKey = $requestIp . '-' . $ip . '-v4';
        if (null !== $cacheValue = self::getCacheResult($cacheKey)) {
            return $cacheValue;
        }

        if (! filter_var($requestIp, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)) {
            return self::setCacheResult($cacheKey, false);
        }

        if (str_contains($ip, '/')) {
            [$address, $netmask] = explode('/', $ip, 2);

            if ($netmask === '0') {
                return self::setCacheResult($cacheKey, filter_var($address, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4) !== false);
            }

            if ($netmask < 0 || $netmask > 32) {
                return self::setCacheResult($cacheKey, false);
            }
        } else {
            $address = $ip;
            $netmask = 32;
        }

        if (ip2long($address) === false) {
            return self::setCacheResult($cacheKey, false);
        }

        return self::setCacheResult($cacheKey, substr_compare(\sprintf('%032b', ip2long($requestIp)), \sprintf('%032b', ip2long($address)), 0, $netmask) === 0);
    }

    /**
     * Determines if an IPv6 address matches or is contained within a given IPv6 subnet.
     *
     * Supports CIDR notation for subnets. Returns true if the request IP is within the specified subnet or matches the address exactly. Throws a RuntimeException if IPv6 support is unavailable in the PHP environment.
     *
     * @param string $requestIp IPv6 address to check.
     * @param string $ip IPv6 address or subnet in CIDR notation.
     * @return bool True if the request IP is within the subnet or matches the address; false otherwise.
     * @throws \RuntimeException If IPv6 support is not enabled in PHP.
     */
    public static function checkIp6(string $requestIp, string $ip): bool
    {
        $cacheKey = $requestIp . '-' . $ip . '-v6';
        if (null !== $cacheValue = self::getCacheResult($cacheKey)) {
            return $cacheValue;
        }

        if (! ((\extension_loaded('sockets') && \defined('AF_INET6')) || @inet_pton('::1'))) {
            throw new \RuntimeException('Unable to check Ipv6. Check that PHP was not compiled with option "disable-ipv6".');
        }

        // Check to see if we were given a IP4 $requestIp or $ip by mistake
        if (! filter_var($requestIp, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6)) {
            return self::setCacheResult($cacheKey, false);
        }

        if (str_contains($ip, '/')) {
            [$address, $netmask] = explode('/', $ip, 2);

            if (! filter_var($address, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6)) {
                return self::setCacheResult($cacheKey, false);
            }

            if ($netmask === '0') {
                return (bool) unpack('n*', @inet_pton($address));
            }

            if ($netmask < 1 || $netmask > 128) {
                return self::setCacheResult($cacheKey, false);
            }
        } else {
            if (! filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6)) {
                return self::setCacheResult($cacheKey, false);
            }

            $address = $ip;
            $netmask = 128;
        }

        $bytesAddr = unpack('n*', @inet_pton($address));
        $bytesTest = unpack('n*', @inet_pton($requestIp));

        if (! $bytesAddr || ! $bytesTest) {
            return self::setCacheResult($cacheKey, false);
        }

        for ($i = 1, $ceil = ceil($netmask / 16); $i <= $ceil; ++$i) {
            $left = $netmask - 16 * ($i - 1);
            $left = ($left <= 16) ? $left : 16;
            $mask = ~(0xFFFF >> $left) & 0xFFFF;
            if (($bytesAddr[$i] & $mask) !== ($bytesTest[$i] & $mask)) {
                return self::setCacheResult($cacheKey, false);
            }
        }

        return self::setCacheResult($cacheKey, true);
    }

    /****
     * Anonymizes an IP address by zeroing out the last bytes.
     *
     * By default, removes the last byte of IPv4 addresses and the last 8 bytes of IPv6 addresses, replacing them with zeros to obscure the host portion. Handles IPv6 zone identifiers and bracketed IPv6 notation. Throws an InvalidArgumentException if the number of bytes to anonymize is negative or exceeds the address length (4 for IPv4, 16 for IPv6).
     *
     * @param string $ip The IP address to anonymize. Supports IPv4, IPv6, and IPv6-mapped IPv4 addresses.
     * @param int $v4Bytes [optional] Number of trailing bytes to anonymize for IPv4 addresses (default 1).
     * @param int $v6Bytes [optional] Number of trailing bytes to anonymize for IPv6 addresses (default 8).
     * @return string The anonymized IP address.
     *
     * @throws \InvalidArgumentException If the number of bytes to anonymize is negative or exceeds the address length.
     */
    public static function anonymize(string $ip/* , int $v4Bytes = 1, int $v6Bytes = 8 */): string
    {
        $v4Bytes = 1 < \func_num_args() ? func_get_arg(1) : 1;
        $v6Bytes = 2 < \func_num_args() ? func_get_arg(2) : 8;

        if ($v4Bytes < 0 || $v6Bytes < 0) {
            throw new \InvalidArgumentException('Cannot anonymize less than 0 bytes.');
        }

        if ($v4Bytes > 4 || $v6Bytes > 16) {
            throw new \InvalidArgumentException('Cannot anonymize more than 4 bytes for IPv4 and 16 bytes for IPv6.');
        }

        /*
         * If the IP contains a % symbol, then it is a local-link address with scoping according to RFC 4007
         * In that case, we only care about the part before the % symbol, as the following functions, can only work with
         * the IP address itself. As the scope can leak information (containing interface name), we do not want to
         * include it in our anonymized IP data.
         */
        if (str_contains($ip, '%')) {
            $ip = mb_substr($ip, 0, mb_strpos($ip, '%'));
        }

        $wrappedIPv6 = false;
        if (str_starts_with($ip, '[') && str_ends_with($ip, ']')) {
            $wrappedIPv6 = true;
            $ip = mb_substr($ip, 1, -1);
        }

        $mappedIpV4MaskGenerator = static function (string $mask, int $bytesToAnonymize) {
            $mask .= str_repeat('ff', 4 - $bytesToAnonymize);
            $mask .= str_repeat('00', $bytesToAnonymize);

            return '::' . implode(':', mb_str_split($mask, 4));
        };

        $packedAddress = inet_pton($ip);
        if (mb_strlen($packedAddress) === 4) {
            $mask = rtrim(str_repeat('255.', 4 - $v4Bytes) . str_repeat('0.', $v4Bytes), '.');
        } elseif ($ip === inet_ntop($packedAddress & inet_pton('::ffff:ffff:ffff'))) {
            $mask = $mappedIpV4MaskGenerator('ffff', $v4Bytes);
        } elseif ($ip === inet_ntop($packedAddress & inet_pton('::ffff:ffff'))) {
            $mask = $mappedIpV4MaskGenerator('', $v4Bytes);
        } else {
            $mask = str_repeat('ff', 16 - $v6Bytes) . str_repeat('00', $v6Bytes);
            $mask = implode(':', mb_str_split($mask, 4));
        }
        $ip = inet_ntop($packedAddress & inet_pton($mask));

        if ($wrappedIPv6) {
            $ip = '[' . $ip . ']';
        }

        return $ip;
    }

    /****
     * Determines if the given IP address is within any of the predefined private or special-use subnets.
     *
     * @param string $requestIp The IP address to check.
     * @return bool True if the IP address is private or special-use, false otherwise.
     */
    public static function isPrivateIp(string $requestIp): bool
    {
        return self::checkIp($requestIp, self::PRIVATE_SUBNETS);
    }

    /**
     * Retrieves a cached boolean result for the specified cache key, or null if not present.
     *
     * If the result exists, it is moved to the end of the cache to implement LRU (Least Recently Used) behavior.
     *
     * @param string $cacheKey The key identifying the cached result.
     * @return bool|null The cached boolean result, or null if not found.
     */
    private static function getCacheResult(string $cacheKey): ?bool
    {
        if (isset(self::$checkedIps[$cacheKey])) {
            // Move the item last in cache (LRU)
            $value = self::$checkedIps[$cacheKey];
            unset(self::$checkedIps[$cacheKey]);
            self::$checkedIps[$cacheKey] = $value;

            return self::$checkedIps[$cacheKey];
        }

        return null;
    }

    /**
     * Stores a boolean result in the internal cache under the specified key, trimming the cache if it exceeds 1000 entries.
     *
     * @param string $cacheKey The key under which to store the result.
     * @param bool $result The result to cache.
     * @return bool The cached result.
     */
    private static function setCacheResult(string $cacheKey, bool $result): bool
    {
        if (1000 < \count(self::$checkedIps)) {
            // stop memory leak if there are many keys
            self::$checkedIps = \array_slice(self::$checkedIps, 500, null, true);
        }

        return self::$checkedIps[$cacheKey] = $result;
    }
}

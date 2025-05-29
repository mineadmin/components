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

namespace Mine\Support\Request;

use Hyperf\HttpServer\Request;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * @mixin Request
 */
trait ClientIpRequestTrait
{
    /**
     * @var string[]
     */
    protected static array $trustedProxies = [];

    private static int $trustedHeaderSet = -1;

    private array $trustedValuesCache = [];

    private bool $isForwardedValid = true;

    private static bool $isTrustedRemoteAddr = false;

    /**
     * Indicates whether the remote address is currently considered a trusted proxy.
     *
     * @return bool True if 'REMOTE_ADDR' is marked as trusted; otherwise, false.
     */
    public static function isTrustedRemoteAddr(): bool
    {
        return self::$isTrustedRemoteAddr;
    }

    /**
     * Resets the trusted remote address flag to false.
     *
     * This method marks the remote address as not trusted for subsequent client IP resolution.
     */
    public static function resetTrustedRemoteAddr(): void
    {
        self::$isTrustedRemoteAddr = false;
    }

    /**
     * Configures the list of trusted proxies and the trusted header set.
     *
     * Recognizes special entries such as 'REMOTE_ADDR' to mark the remote address as trusted,
     * and 'PRIVATE_SUBNETS' or 'private_ranges' to include all private subnet ranges.
     *
     * @param array $proxies List of trusted proxy IP addresses, ranges, or special identifiers.
     * @param int $trustedHeaderSet Bitmask indicating which proxy headers are trusted.
     */
    public static function setTrustedProxies(array $proxies, int $trustedHeaderSet): void
    {
        if (false !== $i = array_search('REMOTE_ADDR', $proxies, true)) {
            self::$isTrustedRemoteAddr = true;
        }

        if (false !== ($i = array_search('PRIVATE_SUBNETS', $proxies, true)) || false !== ($i = array_search('private_ranges', $proxies, true))) {
            unset($proxies[$i]);
            $proxies = array_merge($proxies, IpUtils::PRIVATE_SUBNETS);
        }

        self::$trustedProxies = $proxies;
        self::$trustedHeaderSet = $trustedHeaderSet;
    }

    /**
     * Retrieves an array of client IP addresses ordered from most to least trusted.
     *
     * If the request does not originate from a trusted proxy, returns an array containing only the remote address. Otherwise, extracts and returns client IPs from trusted proxy headers, excluding trusted proxies themselves. The most trusted client IP appears first in the array.
     *
     * @return array List of client IP addresses, with the most trusted first.
     */
    public function getClientIps(): array
    {
        $ip = $this->server('REMOTE_ADDR');

        if (! $this->isFromTrustedProxy()) {
            return [$ip];
        }

        return $this->getTrustedValues(ClientIpRequestConstant::HEADER_X_FORWARDED_FOR, $ip) ?: [$ip];
    }

    /**
     * Determines if the request was made through a trusted proxy.
     *
     * Returns true if the remote address matches any configured trusted proxy or if the remote address is explicitly marked as trusted.
     *
     * @return bool True if the request is from a trusted proxy; otherwise, false.
     */
    public function isFromTrustedProxy(): bool
    {
        return (self::$trustedProxies
        && IpUtils::checkIp($this->server('REMOTE_ADDR', ''), self::$trustedProxies))
        || self::isTrustedRemoteAddr();
    }

    /**
     * Retrieves and parses trusted proxy header values for a specified header type.
     *
     * Extracts values from trusted proxy headers (such as X-Forwarded-For or Forwarded) based on the configured trusted header set. Normalizes and filters the values if an IP is provided, and caches results for performance. Throws a RuntimeException if conflicting or invalid Forwarded headers are detected.
     *
     * @param int $type The header type constant to extract values for.
     * @param string|null $ip The remote IP address for normalization and filtering, or null to skip filtering.
     * @return array The list of trusted values extracted from the relevant headers.
     * @throws \RuntimeException If the Forwarded header is invalid or contains conflicting information.
     */
    private function getTrustedValues(int $type, ?string $ip = null): array
    {
        $cacheKey = $type . "\0" . ((self::$trustedHeaderSet & $type) ? $this->getHeaderLine(ClientIpRequestConstant::TRUSTED_HEADERS[$type]) : '');
        $cacheKey .= "\0" . $ip . "\0" . $this->getHeaderLine(ClientIpRequestConstant::TRUSTED_HEADERS[ClientIpRequestConstant::HEADER_FORWARDED]);

        if (isset($this->trustedValuesCache[$cacheKey])) {
            return $this->trustedValuesCache[$cacheKey];
        }

        $clientValues = [];
        $forwardedValues = [];

        if ((self::$trustedHeaderSet & $type) && $this->hasHeader(ClientIpRequestConstant::TRUSTED_HEADERS[$type])) {
            foreach (explode(',', $this->getHeaderLine(ClientIpRequestConstant::TRUSTED_HEADERS[$type])) as $v) {
                $clientValues[] = ($type === ClientIpRequestConstant::HEADER_X_FORWARDED_PORT ? '0.0.0.0:' : '') . trim($v);
            }
        }

        if ((self::$trustedHeaderSet & ClientIpRequestConstant::HEADER_FORWARDED) && (isset(ClientIpRequestConstant::FORWARDED_PARAMS[$type])) && $this->hasHeader(ClientIpRequestConstant::TRUSTED_HEADERS[ClientIpRequestConstant::HEADER_FORWARDED])) {
            $forwarded = $this->getHeaderLine(ClientIpRequestConstant::TRUSTED_HEADERS[ClientIpRequestConstant::HEADER_FORWARDED]);
            $parts = HeaderUtils::split($forwarded, ',;=');
            $param = ClientIpRequestConstant::FORWARDED_PARAMS[$type];
            foreach ($parts as $subParts) {
                if (null === $v = HeaderUtils::combine($subParts)[$param] ?? null) {
                    continue;
                }
                if ($type === ClientIpRequestConstant::HEADER_X_FORWARDED_PORT) {
                    if (str_ends_with($v, ']') || false === $v = mb_strrchr($v, ':')) {
                        $v = $this->isSecure() ? ':443' : ':80';
                    }
                    $v = '0.0.0.0' . $v;
                }
                $forwardedValues[] = $v;
            }
        }

        if ($ip !== null) {
            $clientValues = $this->normalizeAndFilterClientIps($clientValues, $ip);
            $forwardedValues = $this->normalizeAndFilterClientIps($forwardedValues, $ip);
        }

        if ($forwardedValues === $clientValues || ! $clientValues) {
            return $this->trustedValuesCache[$cacheKey] = $forwardedValues;
        }

        if (! $forwardedValues) {
            return $this->trustedValuesCache[$cacheKey] = $clientValues;
        }

        if (! $this->isForwardedValid) {
            return $this->trustedValuesCache[$cacheKey] = $ip !== null ? ['0.0.0.0', $ip] : [];
        }
        $this->isForwardedValid = false;
        throw new \RuntimeException('The Forwarded header is invalid. Please check your server configuration.');
    }

    /**
     * Normalizes and filters a list of client IP addresses, removing trusted proxies and invalid entries.
     *
     * Appends the actual remote IP to the chain, strips ports and brackets from IPv4/IPv6 addresses, removes invalid or trusted proxy IPs, and returns the remaining untrusted IPs in order from most to least trusted. If all IPs are trusted, returns the first trusted IP as a fallback.
     *
     * @param array $clientIps List of IP addresses extracted from proxy headers.
     * @param string $ip The remote IP address from which the request was received.
     * @return array Filtered and normalized list of client IPs, ordered from most to least trusted.
     */
    private function normalizeAndFilterClientIps(array $clientIps, string $ip): array
    {
        if (! $clientIps) {
            return [];
        }
        $clientIps[] = $ip; // Complete the IP chain with the IP the request actually came from
        $firstTrustedIp = null;

        foreach ($clientIps as $key => $clientIp) {
            if (mb_strpos($clientIp, '.')) {
                // Strip :port from IPv4 addresses. This is allowed in Forwarded
                // and may occur in X-Forwarded-For.
                $i = mb_strpos($clientIp, ':');
                if ($i) {
                    $clientIps[$key] = $clientIp = mb_substr($clientIp, 0, $i);
                }
            } elseif (str_starts_with($clientIp, '[')) {
                // Strip brackets and :port from IPv6 addresses.
                $i = mb_strpos($clientIp, ']', 1);
                $clientIps[$key] = $clientIp = mb_substr($clientIp, 1, $i - 1);
            }

            if (! filter_var($clientIp, \FILTER_VALIDATE_IP)) {
                unset($clientIps[$key]);

                continue;
            }

            if (IpUtils::checkIp($clientIp, self::$trustedProxies)) {
                unset($clientIps[$key]);

                // Fallback to this when the client IP falls into the range of trusted proxies
                $firstTrustedIp ??= $clientIp;
            }
        }

        // Now the IP chain contains only untrusted proxies and the client IP
        return $clientIps ? array_reverse($clientIps) : [$firstTrustedIp];
    }
}

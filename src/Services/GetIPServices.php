<?php

namespace LoginControl\src\Services;

use Exception;

class GetIPServices
{
    /**
     *  Get Remote IP address, behind VPN, load balancer, or even local
     */
    public static function getIP(): string
    {
        if (getenv("HTTP_CLIENT_IP")
            && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")
        ) {
            $ip = getenv("HTTP_CLIENT_IP");
        } elseif (getenv("HTTP_X_FORWARDED_FOR")
            && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")
        ) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } elseif (getenv("REMOTE_ADDR")
            && strcasecmp(getenv("REMOTE_ADDR"), "unknown")
        ) {
            $ip = getenv("REMOTE_ADDR");
        } elseif (isset($_SERVER['REMOTE_ADDR'])
            && $_SERVER['REMOTE_ADDR']
            && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")
        ) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = "unknown";
        }
        return $ip;
    }

    public static function getRealIP(): string
    {
        //this works for someone behind firewall using an API to look inward on network
        try {
            $ipAddress = file_get_contents('https://api.ipify.org');
        } catch (Exception $e ) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }
        return $ipAddress;
    }
}

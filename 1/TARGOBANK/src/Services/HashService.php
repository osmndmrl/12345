<?php

namespace TARGOBANK\Services;

class HashService
{
    public function generateHash($queryString, $key)
    {
        return hash_hmac('sha256', $queryString, $key);
    }
}

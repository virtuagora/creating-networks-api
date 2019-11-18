<?php

namespace App\Util;

class Utils
{
    const LOGFLAG = 1;
    const AUTHFLAG = 2;
    const VALIDATIONFLAG = 4;

    static public function randomStr($length, $keyspace = '123456789ABCDEFGHJKLMNPQRSTUVWXYZ')
    {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }

    static public function arrayWhiteList($array, $list) {
        return array_intersect_key($array, array_flip($list));
    }

    static public function traceStr($str)
    {
        return preg_replace('/[^[:alnum:]]/ui', '', $str);
    }

    static public function sanitizedIdParam($attr, $params)
    {
        $isDigit = ctype_digit($params[$attr] ?? 'x');
        return $isDigit ? $params[$attr] : -1;
    }

    static public function sanitizedStrParam($attr, $params)
    {
        // TODO hacer validacion de verdad
        return $params[$attr] ?? null;
    }

    static public function prepareData($schema, $data, $style = 'simple')
    {
        $newData = [];
        $defaults = [];
        if (isset($schema['properties'])) {
            foreach ($schema['properties'] as $attr => $rule) {
                if (isset($rule['default'])) {
                    $newData[$attr] = $rule['default'];
                }
            }
            foreach ($data as $prop => $val) {
                if (isset($schema['properties'][$prop]['type'])) {
                    $type = $schema['properties'][$prop]['type'];
                    if ($type == 'array') {
                        $newData[$prop] = self::castArray($val, $style);
                    } elseif ($type == 'object') {
                        $newData[$prop] = $val;
                    } else {
                        $newData[$prop] = self::castPrimitive($val);
                    }
                }
            }
        }
        
        return $newData;
    }

    static public function castPrimitive($v)
    {
        if (is_numeric($v)) {
            if (strpos('.', $v) !== false) {
                return floatval($v);
            } else {
                return intval($v);
            }
        } elseif ($v === '' || $v === 'NULL') {
            return null;
        } elseif ($v === 'TRUE') {
            return true;
        } elseif ($v === 'FALSE') {
            return false;
        }
        return (string) $v;
    }

    static public function castArray($v, $style)
    {
        $delimiters = [
            'spaceDelimited' => ' ',
            'pipeDelimited' => '|',
            'simple' => ',',
        ];
        $d = $delimiters[$style] ?? ',';
        $a = explode($d, $v);
        return array_map(['self', 'castPrimitive'], $a);
    }
    
    // static public function checkBefore($date) {
    //     $deadline = Carbon::parse($date);
    //     $today = Carbon::now();
    //     if ($today->gt($deadline)) {
    //         throw new AppException('Application period is over');
    //     }
    // }
}
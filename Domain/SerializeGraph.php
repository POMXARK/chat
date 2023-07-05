<?php

namespace Domain;

class SerializeGraph
{
    /**
     * @param  array  $obj
     * @param         $key
     * @param         $value
     *
     * @return array
     */
    public static function _serialize(array $obj, $key, $value): array
    {
        if (isset($obj[$key])) {
            $obj[$key][] = $value;
        } else {
            $obj[$key] = [$value];
        }
        return $obj;
    }

    public static function getGraphData(array $data): array
    {
        $obj = [];

        foreach ($data as $values) {
            foreach ($values as $key => $value) {
                if (isset($obj[$key])) {
                    $obj[$key][] = $value;
                } else {
                    $obj[$key] = [$value];
                }
            }
        }

        foreach ($obj as $key => $value) {
            $series = [];
            if (isset('name'[$key])) {
                $series['name'] = $key;
                $series['data'] = $value;
            } else {
                $series['name'] = $key;
                $series['data'] = $value;
            }
            $out[] = $series;
        }

        return $out;
    }

    public static function _getGraphData(array $data): array
    {
//        $categories = [];
        $series = [];
        foreach ($data as $values)
            foreach ($values as $key => $value)
                switch ($key) {
//                    case 'date':
//                        $categories = self::_serialize($categories, $key, $value);
//                        break;
                    default:
                        $series = self::_serialize($series, $key, $value);
                }

//        $out = [...$categories];
        $out['series'] = [...$series];
        return self::serializeSeries($out);
    }

    private static function serializeSeries(array $data): array {
        $out[] = [];

        foreach ($data['series'] as $key => $value) {
            $obj = [];
            if (isset('name'[$key])) {
                $obj['name'] = $key;
                $obj['data'] = $value;
            } else {
                $obj['name'] = $key;
                $obj['data'] = $value;
            }
            $out[] = $obj;
        }

        return $out;
    }

}


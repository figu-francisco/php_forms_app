<?php
abstract class MyTools
{
    public static function time_elapsed_string(string $datetime): string
    {
        $now = new DateTime;
        $then = new DateTime($datetime);
        $diff = (array)$now->diff($then);

        $diff['w'] = floor($diff['d'] / 7);
        $diff['d'] -= $diff['w'] * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );

        foreach ($string as $k => & $v) {
            if ($diff[$k]) {
                $v = $diff[$k] . ' ' . $v . ($diff[$k] > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

    public static function merge_search_and_encode_ajax(string $filter, array $choice_color): string
    {
        return Base64Helper::url_safe_encode([ "texte" => $filter, "color" => $choice_color]);
    }

    public static function merge_search_and_encode_no_script(string $filter, array $choice_color): string
    {
        return Base64Helper::url_safe_encode([ "texte" => $filter, "color" => $choice_color, "noscript" => "true"]);
    }

}
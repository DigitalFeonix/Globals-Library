<?php

function get_search_terms($ref)
{
    // search keys in priority order
    $arr = array(
        'q',            // most search engines have standarized on this
        'p',            // yahoo's
        '_nkw',         // ebay
        'query',
        'keywords',
        'keyword',
        'search',
        'searchfor',
        'qkw',
        'qt',
        'k',
        'aqa',
        'as_q',
        'utm_term'      // urchin tracking (last ditch)
        );
    $url = parse_url($ref);
    $key = '';

    if (array_key_exists('query', $url) && ($url['query'] != ''))
    {
        parse_str($url['query'], $str);

        foreach ($arr as $k)
        {
            if (array_key_exists($k, $str))
            {
                $key = trim(strtolower($str[$k]));
                break 1;
            }
        }
    }

    return $key;
}



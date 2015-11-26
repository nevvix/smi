<?php

/**
 * Create <ul> list of social media icons.
 *
 * Usage examples:
 *     echo smi();
 *     echo smi(['px'=>"32"]);
 *     echo smi(['icons'=>["Facebook","Twitter","Email"]]);
 *     echo smi('{"px": "24", "icons": ["Facebook","Twitter","Email"]}');
 * @param array $args
 * @return string
 */
function smi($args=NULL) {
    return SMI::html($args);
}

class SMI {

    static protected $js_urls = [], $icons_data, $links_data;

    static function html($args=NULL) {

        // Merge args
        $default_args = [
            'all'       => FALSE,
            'icons'     => NULL,
            'px'        => "16",
            'space'     => "0", // http://www.w3.org/TR/CSS2/box.html#value-def-margin-width
            'templates' => [
                'ul'     => '<ul class="{{ul_class}}">{{lis}}</ul>',
                'li'     => '    <li style="background-color:{{color}}; border-color:{{color}}; margin-right:{{space}};">{{a}}</li>',
                'a'      => '{{script}}<a{{attrs}} rel="nofollow">{{svg}}</a>',
                'script' => '<script async src="{{js}}"></script>',
            ],
            'filenames' => [
                'icons' => __DIR__."/simpleicons.json",
                'links' => __DIR__."/smi.json",
            ],
        ];
        if (is_string($args)) $args = json_decode($args, TRUE);
        extract(array_merge($default_args, (array)$args)); // $all, $icons, $px, $space, $templates, $filenames

        // Parse & cache json data
        if (!isset(self::$icons_data)) self::$icons_data = self::json_read($filenames['icons']);
        if (!isset(self::$links_data)) self::$links_data = self::json_read($filenames['links']);

        // Filter icons set
        if (!isset($icons)) $icons = (bool)$all ? array_keys(self::$icons_data) : array_keys(self::$links_data);
        $icons_set = self::array_keep(self::$icons_data, $icons);
        $links_set = self::array_keep(self::$links_data, $icons);
        $icons_array = array_merge_recursive($icons_set, $links_set);
 
        // Build HTML snippet keeping the order in $icons
        $lis = [];
        foreach ($icons as $icon) {

            $data = $icons_array[$icon]; // keys = 'title', 'class', 'color', 'svg', 'js', 'onclick', 'url', 'href'

            // js & <script>
            $data['script'] = NULL;
            if (isset($data['js'])) {
                // Cache js urls to load <script> only once on the page
                if (!in_array($data['js'], self::$js_urls)) {
                    self::$js_urls []= $data['js'];
                    $data['script'] = self::tr($templates['script'], ['js'=>$data['js']]);
                }
            }

            // href
            if (isset($data['href'])) $data['href'] = self::encode_query($data['href']);

            // url & onclick
            if (isset($data['url'])) $data['onclick'] = self::tr($data['onclick'], ['url'=>self::encode_query($data['url'])]);

            // attrs
            $data['attrs'] = self::attrs(self::array_remove($data, ['color', 'svg', 'js', 'script', 'url']));

            // <a>
            $data['a'] = self::tr($templates['a'], $data);

            // <li>
            $lis []= self::tr($templates['li'], $data + compact('space'));
        }

        // <ul>
        $replacements = [
            'ul_class' => "smi-".$px,
            'lis'      => "\n".join("\n", $lis)."\n",
        ];
        return self::tr($templates['ul'], $replacements)."\n";
    }

    static function json_read($filename) {
        return json_decode(@file_get_contents($filename), TRUE);
    }

    static function array_keep($array, $keys) {
        return array_intersect_key($array, array_flip($keys));
    }

    static function array_remove($array, $keys) {
        return array_diff_key($array, array_flip($keys));
    }

    static function encode_query($url, $enc_type=PHP_QUERY_RFC3986) {
        if (!($query = parse_url($url, PHP_URL_QUERY))) return $url;
        list($path, $fragment) = explode($query, $url, 2);
        parse_str($query, $query_data);
        $query = http_build_query($query_data, "", "&", $enc_type);
        return join([$path, $query, $fragment]);
    }

    static function attrs($attributes, $attrs=[]) {
        foreach ((array)$attributes as $key => $value)
            $attrs []= sprintf(' %s="%s"', $key, $value);
        return join($attrs);
    }

    static function tr($string, $replacements=NULL, $replace_pairs=[]) {
        foreach ((array)$replacements as $key => $value)
            $replace_pairs['{{'.$key.'}}'] = $value;
        return strtr($string, $replace_pairs);
    }

}

<?php

/**
 * Create <ul> list of social media icons.
 *
 * Usage examples:
 *     echo smi("si");
 *     echo smi("si", ['px'=>"32"]);
 *     echo smi("si", ['icons'=>["Facebook","Twitter","Gmail"]]);
 *     echo smi("si", '{"px": "24", "icons": ["Facebook","Twitter","Gmail"]}');
 *     echo smi("fa", '{"px": "9", "icons": ["fa-facebook-square","fa-twitter-square","fa-at"]}');
 * @param string $type  Simple Icons or Font Awesome
 * @param string|array $args  JSON string or Array
 * @return string
 */
function smi($type, $args = NULL) {
    $class = [
        "si" => "SMI_SI",
        "fa" => "SMI_FA",
    ];
    $smi = new $class[$type]($args);
    return $smi->html();
}

class SMI {

    protected const JSON_DECODE_FLAGS = JSON_THROW_ON_ERROR | JSON_OBJECT_AS_ARRAY | JSON_BIGINT_AS_STRING;
    protected static $js_urls = [], $icons_data = [], $links_data = [];
    protected $args = [], $icons = [], $icons_array = [];
    public $default_args = [
        'all'       => FALSE,
        'px'        => "16",
        'radius'    => NULL,
        'space'     => "0", // http://www.w3.org/TR/CSS2/box.html#value-def-margin-width
        'templates' => [
            'li'    => '    <li style="margin-right:{{space}};">{{a}}</li>',
            'script' => '<script async src="{{js}}"></script>',
        ],
    ];

    function __construct($args = NULL) {
        $this->args = $this->args_replace_recursive($this->default_args, $this->args, $args);
        extract($this->args); // $all, $px, $radius, $space, $type, $templates, $filenames

        // Parse & cache json data
        if (empty(self::$icons_data[$type])) {
            self::$icons_data[$type] = $this->json_decode_file($filenames['icons']);
        }
        if (empty(self::$links_data[$type])) {
            self::$links_data[$type] = $this->json_decode_file($filenames['links']);
        }

        // Filter icons set
        $this->icons = (bool)$all ? array_keys(self::$icons_data[$type]) : array_keys(self::$links_data[$type]);
        $icons_set = $this->array_keep(self::$icons_data[$type], $this->icons);
        $links_set = $this->array_keep(self::$links_data[$type], $this->icons);
        $this->icons_array = array_replace_recursive($icons_set, $links_set);
    }

    protected function args_replace_recursive() {
        try {
            $replacements = [];
            $func_get_args = func_get_args();
            foreach (array_filter($func_get_args) as $args) {
                if (is_string($args)) {
                    $args = json_decode($args, NULL, 512, self::JSON_DECODE_FLAGS);
                }
                $replacements []= $args;
            }
            return array_replace_recursive(...$replacements);
        }
        catch (JsonException $e) {
            echo 'JsonException: ', $e->getMessage(), PHP_EOL, $e->getTraceAsString();
            exit;
        }
    }

    protected function json_decode_file($filename, $flags = 0) {
        return is_readable($filename) ? json_decode(file_get_contents($filename), NULL, 512, $flags|self::JSON_DECODE_FLAGS) : NULL;
    }

    protected function array_keep(array $array, array $keys) {
        return array_intersect_key($array, array_flip($keys));
    }

    protected function array_remove(array $array, array $keys) {
        return array_diff_key($array, array_flip($keys));
    }

    protected function encode_query($url) {
        if ($query = parse_url($url, PHP_URL_QUERY)) {
            [$path] = explode($query, $url, 2);
            parse_str($query, $data);
            $query = http_build_query($data, "", NULL, PHP_QUERY_RFC3986);
            if ($fragment = parse_url($url, PHP_URL_FRAGMENT)) {
                $fragment = '#'.rawurlencode($fragment);
            }
            return join([$path, $query, $fragment]);
        }
        return $url;
    }

    protected function attributes(array $attributes) {
        return join(' ', array_map(fn($key, $value) => sprintf('%s="%s"', $key, $value), array_keys($attributes), array_values($attributes)));
    }

    protected function tr($string, array $replacements = []) {
        if ($replacements) {
            $keys = array_map(fn($key) => '{{'.$key.'}}', array_keys($replacements));
            $replacements = array_combine($keys, array_values($replacements));
            return strtr($string, $replacements);
        }
        return $string;
    }
}

class SMI_SI extends SMI {

    protected $args = [
        'type' => "si",
        'templates' => [
            'ul'     => "<ul class=\"smi-si-{{px}}\">\n{{lis}}\n</ul>",
            'a'      => '{{script}}<a {{attrs}} rel="nofollow">{{svg}}</a>',
            'color'  => 'background-color:{{color}};border-color:{{color}};',
            'radius' => 'border-radius:{{radius}};',
        ],
        'filenames' => [
            'icons' => __DIR__.'/si.json',
            'links' => __DIR__.'/si/smi.json',
        ],
    ];

    function html() {
        extract($this->args); // $all, $px, $radius, $space, $type, $templates, $filenames

        // Build HTML snippet keeping the order in $this->icons
        $lis = [];
        foreach ($this->icons as $icon) {

            $data = $this->icons_array[$icon]; // keys = 'title', 'class', 'color', 'svg', 'js', 'onclick', 'url', 'href'

            // js & <script>
            $data['script'] = NULL;
            if (isset($data['js'])) {
                // Cache js urls to load <script> only once on the page
                if (!in_array($data['js'], self::$js_urls)) {
                    self::$js_urls []= $data['js'];
                    $data['script'] = $this->tr($templates['script'], ['js' => $data['js']]);
                }
            }

            // href
            if (isset($data['href'])) {
                $data['href'] = $this->encode_query($data['href']);
            }

            // url & onclick
            if (isset($data['url'])) {
                $data['onclick'] = $this->tr($data['onclick'], ['url' => $this->encode_query($data['url'])]);
            }

            // style
            $data['style'] = $this->tr($templates['color'], ['color' => $data['color']]);
            if (isset($radius)) {
                $data['style'] .= $this->tr($templates['radius'], compact('radius'));
            }

            // attrs
            $data['attrs'] = $this->attributes($this->array_remove($data, ['color', 'svg', 'js', 'script', 'url']));

            // <a>
            $data['a'] = $this->tr($templates['a'], $data);

            // <li>
            $lis []= $this->tr($templates['li'], $data + compact('space'));
        }

        // <ul>
        return $this->tr($templates['ul'], compact('px') + ['lis' => join("\n", $lis)])."\n";
    }

}

class SMI_FA extends SMI {

    protected $args = [
        'type' => "fa",
        'templates' => [
            'ul' => "<ul class=\"smi-fa-{{px}}\">\n{{lis}}\n</ul>",
            'a'  => '{{script}}<a {{attrs}} rel="nofollow">{{i}}</a>',
        ],
        'filenames' => [
            'icons' => __DIR__.'/fa.json',
            'links' => __DIR__.'/fa/smi.json',
        ],
    ];

    function html() {
        extract($this->args); // $all, $px, $radius, $space, $type, $templates, $filenames

        // Build HTML snippet keeping the order in $this->icons
        $lis = [];
        foreach ($this->icons as $icon) {

            $data = $this->icons_array[$icon]; // keys = 'title', 'class', 'i', 'js', 'onclick', 'url', 'href'

            // js & <script>
            $data['script'] = NULL;
            if (isset($data['js'])) {
                // Cache js urls to load <script> only once on the page
                if (!in_array($data['js'], self::$js_urls)) {
                    self::$js_urls []= $data['js'];
                    $data['script'] = $this->tr($templates['script'], ['js' => $data['js']]);
                }
            }

            // href
            if (isset($data['href'])) {
                $data['href'] = $this->encode_query($data['href']);
            }

            // url & onclick
            if (isset($data['url'])) {
                $data['onclick'] = $this->tr($data['onclick'], ['url' => $this->encode_query($data['url'])]);
            }

            // attrs
            $data['attrs'] = $this->attributes($this->array_remove($data, ['class', 'i', 'js', 'script', 'url']));

            // <a>
            $data['a'] = $this->tr($templates['a'], $data);

            // <li>
            $lis []= $this->tr($templates['li'], $data + compact('space'));
        }

        // <ul>
        return $this->tr($templates['ul'], compact('px') + ['lis' => join("\n", $lis)])."\n";
    }

}

<?php

/**
 * Create <ul> list of social media icons.
 *
 * Usage examples:
 *     echo smi("si");
 *     echo smi("si", ['px'=>"32"]);
 *     echo smi("si", ['icons'=>["Facebook","Twitter","Email"]]);
 *     echo smi("si", '{"px": "24", "icons": ["Facebook","Twitter","Email"]}');
 * @param string $type
 * @param array $args
 * @return string
 */
function smi($type, $args=NULL) {
    $class = [
        "si" => "SMI_SI",
        "fa" => "SMI_FA",
    ];
    $smi = new $class[$type]($type, $args);
    return $smi->html();
}

class SMI {

    public $default_args = [
        'all'       => FALSE,
        'icons'     => NULL,
        'px'        => "16",
        'radius'    => NULL,
        'space'     => "0", // http://www.w3.org/TR/CSS2/box.html#value-def-margin-width
        'templates' => [
            'li'    => '    <li style="margin-right:{{space}};">{{a}}</li>',
            'script' => '<script async src="{{js}}"></script>',
        ],
    ];
    static protected $js_urls = [], $icons_data, $links_data;
    protected $icons, $icons_array;

    function __construct($type, $args=NULL) {
        $this->args['filenames']['icons'] = __DIR__.$this->args['filenames']['icons'];
        $this->args['filenames']['links'] = __DIR__.$this->args['filenames']['links'];
        $this->args = $this->merge_args($this->args, $args);
        extract($this->args); // $all, $icons, $px, $radius, $space, $templates, $filenames

        // Parse & cache json data
        if (!isset(self::$icons_data[$type])) self::$icons_data[$type] = $this->json_read($filenames['icons']);
        if (!isset(self::$links_data[$type])) self::$links_data[$type] = $this->json_read($filenames['links']);

        // Filter icons set
        $this->icons = $icons;
        if (!isset($this->icons)) $this->icons = (bool)$all ? array_keys(self::$icons_data[$type]) : array_keys(self::$links_data[$type]);
        $icons_set = $this->array_keep(self::$icons_data[$type], $this->icons);
        $links_set = $this->array_keep(self::$links_data[$type], $this->icons);
        $this->icons_array = array_replace_recursive($icons_set, $links_set);
    }

    protected function merge_args() {
        $param_arr = [$this->default_args];
        foreach (func_get_args() as $args) {
            if (is_string($args)) $args = json_decode($args, TRUE);
            $param_arr []= $args;
        }
        return call_user_func_array("array_replace_recursive", $param_arr);
    }

    protected function json_read($filename) {
        return json_decode(@file_get_contents($filename), TRUE);
    }

    protected function array_keep($array, $keys) {
        return array_intersect_key($array, array_flip($keys));
    }

    protected function array_remove($array, $keys) {
        return array_diff_key($array, array_flip($keys));
    }

    protected function encode_query($url, $enc_type=PHP_QUERY_RFC3986) {
        if (!($query = parse_url($url, PHP_URL_QUERY))) return $url;
        list($path, $fragment) = explode($query, $url, 2);
        parse_str($query, $query_data);
        $query = http_build_query($query_data, "", "&", $enc_type);
        return join([$path, $query, $fragment]);
    }

    protected function attrs($attributes, $attrs=[]) {
        foreach ((array)$attributes as $key => $value)
            $attrs []= sprintf(' %s="%s"', $key, $value);
        return join($attrs);
    }

    protected function tr($string, $replacements=NULL, $replace_pairs=[]) {
        foreach ((array)$replacements as $key => $value)
            $replace_pairs['{{'.$key.'}}'] = $value;
        return strtr($string, $replace_pairs);
    }

}

class SMI_SI extends SMI {

    protected $args = [
        'templates' => [
            'ul'     => "<ul class=\"smi-si-{{px}}\">\n{{lis}}\n</ul>",
            'a'      => '{{script}}<a{{attrs}} rel="nofollow">{{svg}}</a>',
            'color'  => 'background-color:{{color}};border-color:{{color}};',
            'radius' => 'border-radius:{{radius}};',
        ],
        'filenames' => [
            'icons' => "/si.json",
            'links' => "/si/smi.json",
        ],
    ];

    function html() {
        extract($this->args); // $all, $icons, $px, $radius, $space, $templates, $filenames

        // Build HTML snippet keeping the order in $icons
        $lis = [];
        foreach ($this->icons as $icon) {

            $data = $this->icons_array[$icon]; // keys = 'title', 'class', 'color', 'svg', 'js', 'onclick', 'url', 'href'

            // js & <script>
            $data['script'] = NULL;
            if (isset($data['js'])) {
                // Cache js urls to load <script> only once on the page
                if (!in_array($data['js'], self::$js_urls)) {
                    self::$js_urls []= $data['js'];
                    $data['script'] = $this->tr($templates['script'], ['js'=>$data['js']]);
                }
            }

            // href
            if (isset($data['href'])) $data['href'] = $this->encode_query($data['href']);

            // url & onclick
            if (isset($data['url'])) $data['onclick'] = $this->tr($data['onclick'], ['url'=>$this->encode_query($data['url'])]);

            // style
            $data['style'] = $this->tr($templates['color'], ['color'=>$data['color']]);
            if (isset($radius)) $data['style'] .= $this->tr($templates['radius'], compact('radius'));

            // attrs
            $data['attrs'] = $this->attrs($this->array_remove($data, ['radius', 'color', 'svg', 'js', 'script', 'url']));

            // <a>
            $data['a'] = $this->tr($templates['a'], $data);

            // <li>
            $lis []= $this->tr($templates['li'], $data + compact('space'));
        }

        // <ul>
        return $this->tr($templates['ul'], compact('px') + ['lis'=>join("\n", $lis)])."\n";
    }

}

class SMI_FA extends SMI {

    protected $args = [
        'templates' => [
            'ul' => "<ul class=\"smi-fa-{{px}}\">\n{{lis}}\n</ul>",
            'a'  => '{{script}}<a{{attrs}} rel="nofollow">{{i}}</a>',
        ],
        'filenames' => [
            'icons' => "/fa.json",
            'links' => "/fa/smi.json",
        ],
    ];

    function html() {
        extract($this->args); // $all, $icons, $px, $radius, $space, $templates, $filenames

        // Build HTML snippet keeping the order in $icons
        $lis = [];
        foreach ($this->icons as $icon) {

            $data = $this->icons_array[$icon]; // keys = 'title', 'class', 'i', 'js', 'onclick', 'url', 'href'

            // js & <script>
            $data['script'] = NULL;
            if (isset($data['js'])) {
                // Cache js urls to load <script> only once on the page
                if (!in_array($data['js'], self::$js_urls)) {
                    self::$js_urls []= $data['js'];
                    $data['script'] = $this->tr($templates['script'], ['js'=>$data['js']]);
                }
            }

            // href
            if (isset($data['href'])) $data['href'] = $this->encode_query($data['href']);

            // url & onclick
            if (isset($data['url'])) $data['onclick'] = $this->tr($data['onclick'], ['url'=>$this->encode_query($data['url'])]);

            // attrs
            $data['attrs'] = $this->attrs($this->array_remove($data, ['class', 'i', 'js', 'script', 'url']));

            // <a>
            $data['a'] = $this->tr($templates['a'], $data);

            // <li>
            $lis []= $this->tr($templates['li'], $data + compact('space'));
        }

        // <ul>
        return $this->tr($templates['ul'], compact('px') + ['lis'=>join("\n", $lis)])."\n";
    }

}

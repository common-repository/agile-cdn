<?php

class AgileCDN
{
    private $cdn_prefix;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        add_action('template_redirect', array($this, 'template_redirect'));
        add_filter('rewrite_urls',      array($this, 'filter'));
        $cdn_prefix = empty(esc_attr(get_option('agile_cdn_prefix'))) ? '' : esc_attr(get_option('agile_cdn_prefix'));
        $this->cdn_prefix = $this->matchPrefix($cdn_prefix);
    }

    /*
	 * Starting page buffer.
	 */
    public function template_redirect()
    {
        ob_start(array($this, 'ob'));
    }

    /*
	 * Rewriting URLs once buffer ends.
	 *
	 * @return  string  The filtered page output including rewritten URLs.
	 */
    public function ob($contents)
    {
        return apply_filters('rewrite_urls', $contents, $this);
    }

    public function filter($content)
    {   
        return $this->rewrite($content);
    }

    public function rewrite($html)
    {
        $base_url = wp_parse_url((empty(esc_attr(get_option('agile_cdn_url'))) ? get_site_url() : esc_attr(get_option('agile_cdn_url'))));
        $pattern_url = $base_url['scheme'] . '://' . $base_url['host'];
        $pattern = '#[("\']\s*(?<url>(?:(?:https?:|)' . preg_quote($pattern_url, '#') . ')\/(?:(?:(?:' . $this->get_static_paths() . ')[^"\',)]+))|\/[^/](?:[^"\')\s>]+\.[[:alnum:]]+))\s*["\')]#i';

        return preg_replace_callback(
            $pattern,
            function ($matches) {
                $uri = wp_parse_url($matches['url']);
                $query_string = (wp_parse_url($matches['url'], PHP_URL_QUERY) ? '?' . parse_url($matches['url'], PHP_URL_QUERY) : null);
                return str_replace($matches['url'], $this->cdn_prefix . $uri['path'] . $query_string, $matches[0]);
            },
            $html
        );
    }

    /*
	 * Get static resources path
	 */
    private function get_static_paths()
    {
        $wp_content_dirname  = ltrim(trailingslashit(wp_parse_url(content_url(), PHP_URL_PATH)), '/');
        $wp_includes_dirname = ltrim(trailingslashit(wp_parse_url(includes_url(), PHP_URL_PATH)), '/');

        $upload_dirname = '';
        $uploads_info   = wp_upload_dir();

        if (!empty($uploads_info['baseurl'])) {
            $upload_dirname = '|' . ltrim(trailingslashit(wp_parse_url($uploads_info['baseurl'], PHP_URL_PATH)), '/');
        }

        return $wp_content_dirname . $upload_dirname . '|' . $wp_includes_dirname;
    }

    public function matchPrefix($cdn_prefix) {
        // 自定义域名
        if (strpos($cdn_prefix, 'http://') !== false || strpos($cdn_prefix, 'https://') !== false) {
            return rtrim($cdn_prefix, '/');
        } else if (strpos($cdn_prefix, AGILEWING_CDN_DOMAIN_DEV) !== false) {
            $cdn_domain = strpos($cdn_prefix, AGILEWING_CDN_DOMAIN_DEV) !== false ? '' : AGILEWING_CDN_DOMAIN_DEV;
            return 'https://' . $cdn_prefix . $cdn_domain;
        } else {
            $cdn_domain = strpos($cdn_prefix, AGILEWING_CDN_DOMAIN) !== false ? '' : AGILEWING_CDN_DOMAIN;
            return 'https://' . $cdn_prefix . $cdn_domain;
        }
    }
}

if (agile_cdn_is_enabled()) {
    new AgileCDN;
}

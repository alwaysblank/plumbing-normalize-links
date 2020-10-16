<?php

namespace Livy\Plumbing\NormalizeLinks;

class Normalized
{

    protected $url;
    protected $label;
    protected $newTab;
    protected $probablyExternal;
    protected $settings = [
        'label'               => "Learn More",
        'external_in_new_tab' => true,
    ];

    public function __construct($link, $settings = [])
    {
        $this->parseSettings($settings);
        $this->parseLink($link);
    }

    protected function parseSettings($settings)
    {
        if ( ! is_array($settings)) {
            return;
        }

        foreach ($settings as $key => $setting) {
            if (isset($this->settings[$key])) {
                $this->settings[$key] = $setting;
            }
        }
    }

    protected function parseLink($link)
    {
        if ( ! is_array($link)) {
            return;
        }

        if (isset($link['url']) && filter_var($link['url'], FILTER_VALIDATE_URL)) {
            $this->url = $link['url'];
        }

        if (isset($link['title']) && is_string($link['title'])) {
            $this->label = $link['title'];
        } else {
            $this->label = $this->settings['label'];
        }

        if (isset($link['target']) && '_blank' === $link['target']) {
            $this->newTab = true;
        }

        $this->probablyExternal = $this->probablyExternal($this->url);

        if ($this->probablyExternal && true === $this->settings['external_in_new_tab']) {
            $this->newTab = true;
        }
    }

    protected function probablyExternal($url)
    {
        // If link starts with a slash, it's almost certainly local
        if (strpos($url, '/') === 0) {
            return false;
        }

        return parse_url($url, PHP_URL_HOST) !== parse_url(home_url(), PHP_URL_HOST);
    }

    public function url()
    {
        if ($this->valid()) {
            return $this->url;
        }

        return null;
    }

    public function valid(): bool
    {
        return is_string($this->url) && is_string($this->label);
    }

    public function label()
    {
        if ($this->valid()) {
            return $this->label;
        }

        return null;
    }

    public function newTab()
    {
        if ($this->valid()) {
            return $this->newTab;
        }

        return null;
    }

    public function possiblyExternal()
    {
        if ($this->valid()) {
            return $this->possiblyExternal;
        }

        return null;
    }
}

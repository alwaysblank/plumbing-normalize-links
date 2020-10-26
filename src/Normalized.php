<?php

namespace Livy\Plumbing\NormalizeLinks;

class Normalized
{

    protected $url;
    protected $label;
    protected $newTab;
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

    public function validatePart(string $part, array $source)
    {
        if ( ! isset($source[$part])) {
            return false;
        }

        $value = $source[$part];

        switch ($part) {
            case 'url':
                return Validation::url($value);
            case 'title':
                return Validation::title($value);
            case 'target':
                return '_blank' === $value;
            default:
                return false;
        }
    }

    protected function parseLink($link)
    {
        // Make it easy on ourselves and allow simple links
        if (is_string($link)) {
            $link = [
                'url' => $link,
            ];
        }

        if ( ! is_array($link)) {
            return;
        }

        if ($this->validatePart('url', $link)) {
            $this->url = $link['url'];
        }

        if ($this->validatePart('title', $link)) {
            $this->label = $link['title'];
        } else {
            $this->label = $this->settings['label'];
        }

        if ($this->validatePart('target', $link)) {
            $this->newTab = true;
        }
    }

    public function set($key, $value)
    {
        if (in_array($key, ['url', 'label', 'newTab'])) {
            $this->$key = $value;
        }

        return $this;
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
            return $this->newTab || ($this->probablyExternal() && true === $this->settings['external_in_new_tab']);
        }

        return null;
    }

    public function probablyExternal()
    {
        if ($this->valid()) {
            return Validation::probablyExternal($this->url);
        }

        return null;
    }
}

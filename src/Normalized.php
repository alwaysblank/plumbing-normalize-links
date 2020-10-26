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

    protected function validatePart(string $part, array$source)
    {
        if (!isset($source[$part])) {
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
        }

        // Didn't match anything, so not valid
        return false;
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

        $this->evaluateExternality();
    }

    protected function evaluateExternality()
    {
        $this->probablyExternal = Validation::probablyExternal($this->url);
        if ($this->probablyExternal && true === $this->settings['external_in_new_tab']) {
            $this->newTab = true;
        }
    }

    public function set($key, $value)
    {
        if (in_array($key, ['url', 'label', 'newTab'])) {
            switch ($key) {
                case 'url':
                    $this->url = $value;
                    $this->evaluateExternality();
                    break;
                default:
                    $this->$key = $value;
                    break;
            }
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
            return $this->newTab;
        }

        return null;
    }

    public function probablyExternal()
    {
        if ($this->valid()) {
            return $this->probablyExternal;
        }

        return null;
    }
}

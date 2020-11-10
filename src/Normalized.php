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
        'validate'            => [
            'url'   => false,
            'label' => false,
        ]
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

    protected function shouldValidate(string $part)
    {
        if ( ! is_array($this->settings['validate'])) {
            if (true === $this->settings['validate']) {
                // Allow blanket set true
                return true;
            }

            return true;
        }

        // We can assume validate exists
        return isset($this->settings['validate'][$part])
            ? (bool)$this->settings['validate'][$part]
            : false;
    }

    public function validatePart(string $part, array $source)
    {
        if ( ! isset($source[$part])) {
            return false;
        }

        $value = $source[$part];

        switch ($part) {
            case 'url':
                return $this->shouldValidate('url')
                    ? Validation::url($value)
                    : true;
            case 'title':
                return $this->shouldValidate('label')
                    ? Validation::title($value)
                    : true;
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
        $url   = true;
        $label = true;
        if ($this->shouldValidate('url')) {
            $url = Validation::url($this->url);
        }
        if ($this->shouldValidate($label)) {
            $label = Validation::title($this->label);
        }

        return $url && $label;
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

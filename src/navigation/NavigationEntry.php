<?php
/**
 * @author    aelix framework <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */
declare(strict_types = 1);

namespace aelix\framework\navigation;


use aelix\framework\template\ITemplatable;

class NavigationEntry implements ITemplatable
{

    /**
     * @var string internal name of the nav entry
     */
    protected $key = '';

    /**
     * @var string name of the nav entry that will be printed
     */
    protected $name = '';

    /**
     * @var string hyperlink url
     */
    protected $url = '';

    /**
     * @var bool is this currently active? is this the page we're on right now?
     */
    protected $active = false;

    /**
     * NavigationEntry constructor.
     * @param string $key
     * @param string $name
     * @param string $url
     * @param bool $active
     */
    public function __construct(string $key, string $name, string $url, bool $active = false)
    {
        $this->key = $key;
        $this->name = $name;
        $this->url = $url;
        $this->active = $active;
    }

    /**
     * @return array
     */
    public function getTemplateArray(): array
    {
        return [
            'key' => $this->getKey(),
            'url' => $this->getUrl(),
            'name' => $this->getName(),
            'active' => $this->isActive()
        ];
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return NavigationEntry
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return NavigationEntry
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return NavigationEntry
     */
    public function setActive(bool $active = true): self
    {
        $this->active = $active;
        return $this;
    }


}
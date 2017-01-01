<?php
/**
 * @author    aelix framework <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

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
    public function __construct($key, $name, $url, $active = false)
    {
        $this->key = $key;
        $this->name = $name;
        $this->url = $url;
        $this->active = $active;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active = true)
    {
        $this->active = $active;
    }

    /**
     * @return array
     */
    public function getTemplateArray()
    {
        return [
            'key' => $this->getKey(),
            'url' => $this->getUrl(),
            'name' => $this->getName(),
            'active' => $this->isActive()
        ];
    }


}
<?php
/**
 * @author    aelix framework <info@aelix.org>
 * @copyright Copyright (c) 2015 aelix framework
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

namespace aelix\framework\template\extension;


use aelix\framework\util\UString;

class Twig_AelixNavigation extends \Twig_Extension
{

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'aelix navigation';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('generate_navigation', [$this, 'function_generateNavigation'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Generates a <ul> with all nav entries
     * @param array $templateArray array for the navigation
     * @return string
     */
    public function function_generateNavigation($templateArray) {
        $output = '<ul>'.NL;

        foreach ($templateArray['entries'] as $entry) {
            if ($entry['active'] === true) {
                $output .= '    <li class="active">';
            } else {
                $output .= '<li>';
            }

            if (!empty($entry['url'])) {
                $output .= '<a href="' . $entry['url'] . '">';
            }

            $output .= UString::encodeHTML($entry['name']);

            if (!empty($entry['url'])) {
                $output .= '</a>';
            }

            $output .= '</li>'.NL;
        }

        $output .= '</ul>';

        return $output;
    }

}